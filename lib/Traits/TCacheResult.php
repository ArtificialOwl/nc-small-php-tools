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


use daita\NcSmallPhpTools\Model\CacheResult;

/**
 * Trait TCacheTools
 *
 * @package daita\NcSmallPhpTools\Traits
 */
trait TCacheResult {


	/** @var array */
	private $cachedResult = [];


	/**
	 * @param string $item
	 *
	 * @return CacheResult
	 */
	protected function cached(string $item): CacheResult {
		if (array_key_exists($item, $this->cachedResult)) {
			return $this->cachedResult[$item];
		}

		$cached = new CacheResult($item);
		$this->cachedResult[$item] = $cached;

		return $cached;
	}


}

