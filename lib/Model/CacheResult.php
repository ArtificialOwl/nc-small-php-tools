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


namespace daita\NcSmallPhpTools\Model;


use daita\NcSmallPhpTools\Exceptions\NotCachedException;


/**
 * Class CacheResult
 *
 * @package daita\NcSmallPhpTools\Model
 */
class CacheResult {


	/** @var string */
	private $source = '';

	/** @var array */
	private $result = [];


	/**
	 * CacheResult constructor.
	 *
	 * @param $source
	 */
	public function __construct($source) {
		$this->source = $source;
	}


	/**
	 * @return array
	 */
	public function getResult(): array {
		return $this->result;
	}

	/**
	 * @param array $result
	 *
	 * @return CacheResult
	 */
	public function setResult(array $result): self {
		$this->result = $result;

		return $this;
	}


	/**
	 * @param string $value
	 * @param string $stored
	 *
	 * @return CacheResult
	 */
	public function set(string $value, string $stored = 'value'): self {
		$this->setResult([$stored => $value]);

		return $this;
	}

	/**
	 * @param string $stored
	 *
	 * @return string
	 * @throws NotCachedException
	 */
	public function get(string $stored = 'value'): string {
		$result = $this->getResult();
		if (!array_key_exists($stored, $result)) {
			throw new NotCachedException();
		}

		return $result[$stored];
	}


	/**
	 * @param array $value
	 * @param string $stored
	 *
	 * @return CacheResult
	 */
	public function setArray(array $value, string $stored = 'value'): self {
		$this->setResult([$stored => $value]);

		return $this;
	}

	/**
	 * @param string $stored
	 *
	 * @return array
	 * @throws NotCachedException
	 */
	public function getArray(string $stored = 'value'): array {
		$result = $this->getResult();
		if (!array_key_exists($stored, $result)) {
			throw new NotCachedException();
		}

		return $result[$stored];
	}


	/**
	 * @param bool $value
	 * @param string $stored
	 *
	 * @return CacheResult
	 */
	public function setBool(bool $value, string $stored = 'value'): self {
		$this->setResult([$stored => $value]);

		return $this;
	}

	/**
	 * @param string $stored
	 *
	 * @return bool
	 * @throws NotCachedException
	 */
	public function getBool(string $stored = 'value'): bool {
		$result = $this->getResult();
		if (!array_key_exists($stored, $result)) {
			throw new NotCachedException();
		}

		return $result[$stored];
	}


	/**
	 * @param int $value
	 * @param string $stored
	 *
	 * @return CacheResult
	 */
	public function setInt(int $value, string $stored = 'value'): self {
		$this->setResult([$stored => $value]);

		return $this;
	}

	/**
	 * @param string $stored
	 *
	 * @return int
	 * @throws NotCachedException
	 */
	public function getInt(string $stored = 'value'): int {
		$result = $this->getResult();
		if (!array_key_exists($stored, $result)) {
			throw new NotCachedException();
		}

		return $result[$stored];
	}

}

