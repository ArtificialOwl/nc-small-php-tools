<?php declare(strict_types=1);


/**
 * Some small tools for Nextcloud
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2020, Maxence Lange <maxence@artificial-owl.com>
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace daita\NcSmallPhpTools\Traits;


use daita\NcSmallPhpTools\Exceptions\RequestContentException;
use daita\NcSmallPhpTools\Exceptions\RequestNetworkException;
use daita\NcSmallPhpTools\Exceptions\RequestResultNotJsonException;
use daita\NcSmallPhpTools\Exceptions\RequestResultSizeException;
use daita\NcSmallPhpTools\Exceptions\RequestServerException;
use daita\NcSmallPhpTools\Model\Request;


/**
 * Trait TCurlRequest
 *
 * @package daita\NcSmallPhpTools\Traits
 */
trait TRequest {


	/** @var int */
	private $maxDownloadSize = 100;

	/** @var bool */
	private $maxDownloadSizeReached = false;


	/**
	 * @param int $size
	 */
	public function setMaxDownloadSize(int $size): void {
		$this->maxDownloadSize = $size;
	}


	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws RequestContentException
	 * @throws RequestNetworkException
	 * @throws RequestResultNotJsonException
	 * @throws RequestResultSizeException
	 * @throws RequestServerException
	 */
	public function retrieveJson(Request $request): array {
		$result = $this->doRequest($request);

		if (strpos($request->getContentType(), 'application/xrd') === 0) {
			$xml = simplexml_load_string($result);
			$result = json_encode($xml, JSON_UNESCAPED_SLASHES);
		}

		$result = json_decode((string)$result, true);
		if (is_array($result)) {
			return $result;
		}

		throw new RequestResultNotJsonException();
	}


	/**
	 * @param Request $request
	 *
	 * @return mixed
	 * @throws RequestContentException
	 * @throws RequestNetworkException
	 * @throws RequestResultSizeException
	 * @throws RequestServerException
	 */
	public function doRequest(Request $request) {
		$this->maxDownloadSizeReached = false;

		$curl = $this->initRequest($request);

		$this->initRequestGet($request);
		$this->initRequestPost($curl, $request);
		$this->initRequestPut($curl, $request);
		$this->initRequestDelete($curl, $request);

		$this->initRequestHeaders($curl, $request);

		$result = curl_exec($curl);

		if ($this->maxDownloadSizeReached === true) {
			throw new RequestResultSizeException();
		}

		$this->parseRequestResult($curl, $request);

		return $result;
	}


	/**
	 * @param Request $request
	 *
	 * @return resource
	 */
	private function initRequest(Request $request) {

		$curl = $this->generateCurlRequest($request);

		curl_setopt($curl, CURLOPT_USERAGENT, $request->getUserAgent());
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $request->getTimeout());
		curl_setopt($curl, CURLOPT_TIMEOUT, $request->getTimeout());

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, $request->isBinary());

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
		curl_setopt($curl, CURLOPT_NOPROGRESS, false);
		curl_setopt(
		/**
		 * @param $downloadSize
		 * @param int $downloaded
		 * @param $uploadSize
		 * @param int $uploaded
		 *
		 * @return int
		 */
			$curl, CURLOPT_PROGRESSFUNCTION,
			function($downloadSize, int $downloaded, $uploadSize, int $uploaded) {
				if ($downloaded > ($this->maxDownloadSize * (1024 * 1024))) {
					$this->maxDownloadSizeReached = true;

					return 1;
				}

				return 0;
			}
		);

		return $curl;
	}


	/**
	 * @param Request $request
	 *
	 * @return resource
	 */
	private function generateCurlRequest(Request $request) {
		$url = $request->getProtocol() . '://' . $request->getAddress() . $request->getParsedUrl();
		if ($request->getType() !== Request::TYPE_GET) {
			$curl = curl_init($url);
		} else {
			$curl = curl_init($url . '?' . $request->getUrlData());
		}

		return $curl;
	}


	/**
	 * @param Request $request
	 */
	private function initRequestGet(Request $request): void {
		if ($request->getType() !== Request::TYPE_GET) {
			return;
		}
	}


	/**
	 * @param resource $curl
	 * @param Request $request
	 */
	private function initRequestPost($curl, Request $request): void {
		if ($request->getType() !== Request::TYPE_POST) {
			return;
		}

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getDataBody());
	}


	/**
	 * @param resource $curl
	 * @param Request $request
	 */
	private function initRequestPut($curl, Request $request): void {
		if ($request->getType() !== Request::TYPE_PUT) {
			return;
		}

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getDataBody());
	}


	/**
	 * @param resource $curl
	 * @param Request $request
	 */
	private function initRequestDelete($curl, Request $request): void {
		if ($request->getType() !== Request::TYPE_DELETE) {
			return;
		}

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getDataBody());
	}


	/**
	 * @param resource $curl
	 * @param Request $request
	 */
	private function initRequestHeaders($curl, Request $request): void {
		$headers = $request->getHeaders();

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	}


	/**
	 * @param resource $curl
	 * @param Request $request
	 *
	 * @throws RequestContentException
	 * @throws RequestServerException
	 * @throws RequestNetworkException
	 */
	private function parseRequestResult($curl, Request &$request): void {
		$this->parseRequestResultCurl($curl, $request);

		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
		$request->setContentType(($contentType === null) ? '' : $contentType);
		$request->setResultCode($code);

		$this->parseRequestResultCode301($code, $request);
		$this->parseRequestResultCode4xx($code, $request);
		$this->parseRequestResultCode5xx($code, $request);
	}


	/**
	 * @param resource $curl
	 * @param Request $request
	 *
	 * @throws RequestNetworkException
	 */
	private function parseRequestResultCurl($curl, Request $request): void {
		$errno = curl_errno($curl);
		if ($errno > 0) {
			throw new RequestNetworkException(
				$errno . ' - ' . curl_error($curl) . ' - ' . json_encode(
					$request, JSON_UNESCAPED_SLASHES
				), $errno
			);
		}
	}


	/**
	 * @param int $code
	 * @param Request $request
	 *
	 * @throws RequestContentException
	 */
	private function parseRequestResultCode301($code, Request $request): void {
		if ($code === 301) {
			throw new RequestContentException(
				'301 - ' . json_encode($request, JSON_UNESCAPED_SLASHES)
			);
		}
	}


	/**
	 * @param int $code
	 * @param Request $request
	 *
	 * @throws RequestContentException
	 */
	private function parseRequestResultCode4xx(int $code, Request $request): void {
		if ($code === 404 || $code === 410) {
			throw new RequestContentException(
				$code . ' - ' . json_encode($request, JSON_UNESCAPED_SLASHES)
			);
		}
	}


	/**
	 * @param int $code
	 * @param Request $request
	 *
	 * @throws RequestServerException
	 */
	private function parseRequestResultCode5xx(int $code, Request $request): void {
		if ($code === 500) {
			throw new RequestServerException(
				$code . ' - ' . json_encode($request, JSON_UNESCAPED_SLASHES)
			);
		}
	}

}

