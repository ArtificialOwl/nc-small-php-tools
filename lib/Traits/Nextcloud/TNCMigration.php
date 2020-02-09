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


namespace daita\NcSmallPhpTools\Traits\Nextcloud;


/**
 * Class TNCMigration
 *
 * @package daita\NcSmallPhpTools\Db
 */
trait TNCMigration {


	protected function copyTable($orig, $dest): void {
		$connection = \OC::$server->getDatabaseConnection();
		$qb = $connection->getQueryBuilder();

		$qb->select('*')
		   ->from($orig);

		$result = $qb->execute();
		while ($row = $result->fetch()) {

			$copy = $connection->getQueryBuilder();
			$copy->insert($dest);
			$ak = array_keys($row);
			foreach ($ak as $k) {
				if ($row[$k] !== null) {
					$copy->setValue($k, $copy->createNamedParameter($row[$k]));
				}
			}
			$copy->execute();
		}

	}

}

