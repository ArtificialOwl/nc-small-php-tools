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


use daita\NcSmallPhpTools\Traits\TArrayTools;


/**
 * Class Options
 *
 * @package daita\NcSmallPhpTools\Model
 */
class Options {


	use TArrayTools;


	/** @var array */
	private $options = [];


	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return Options
	 */
	public function setOption(string $key, string $value): self {
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	public function getOption(string $key, string $default = ''): string {
		return $this->get($key, $this->options, $default);
	}


	/**
	 * @param string $key
	 * @param int $value
	 *
	 * @return Options
	 */
	public function setOptionInt(string $key, int $value): self {
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * @param string $key
	 * @param int $default
	 *
	 * @return int
	 */
	public function getOptionInt(string $key, int $default = 0): int {
		return $this->getInt($key, $this->options, $default);
	}


	/**
	 * @param string $key
	 * @param float $value
	 *
	 * @return Options
	 */
	public function setOptionFloat(string $key, float $value): self {
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * @param string $key
	 * @param float $default
	 *
	 * @return float
	 */
	public function getOptionFloat(string $key, float $default = 0): float {
		return $this->getFloat($key, $this->options, $default);
	}


	/**
	 * @param string $key
	 * @param array $value
	 *
	 * @return Options
	 */
	public function setOptionArray(string $key, array $value): self {
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * @param string $key
	 * @param array $default
	 *
	 * @return array
	 */
	public function getOptionArray(string $key, array $default = []): array {
		return $this->getArray($key, $this->options, $default);
	}


	/**
	 * @param string $key
	 * @param bool $value
	 *
	 * @return Options
	 */
	public function setOptionBool(string $key, bool $value): self {
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * @param string $key
	 * @param bool $default
	 *
	 * @return bool
	 */
	public function getOptionBool(string $key, bool $default = false): bool {
		return $this->getBool($key, $this->options, $default);
	}

}

