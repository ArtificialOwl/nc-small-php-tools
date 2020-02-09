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


namespace daita\NcSmallPhpTools;


use daita\NcSmallPhpTools\Exceptions\ShellMissingItemException;
use daita\NcSmallPhpTools\Exceptions\ShellUnknownCommandException;
use daita\NcSmallPhpTools\Exceptions\ShellUnknownItemException;

interface IInteractiveShellClient {


	/**
	 * @param string $source
	 * @param string $field
	 *
	 * @return string[]
	 */
	public function fillCommandList(string $source, string $field): array;


	/**
	 * @param string $command
	 *
	 * @throws ShellMissingItemException
	 * @throws ShellUnknownCommandException
	 * @throws ShellUnknownItemException
	 */
	public function manageCommand(string $command): void;


}

