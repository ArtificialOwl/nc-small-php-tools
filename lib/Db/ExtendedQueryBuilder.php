<?php
declare(strict_types=1);


/**
 * Some small tools for Nextcloud.
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2018, Maxence Lange <maxence@artificial-owl.com>
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


namespace daita\NcSmallPhpTools\Db;


use DateInterval;
use DateTime;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Exception;
use OC\DB\QueryBuilder\QueryBuilder;
use OCP\DB\QueryBuilder\IQueryBuilder;


/**
 * Class RequestQueryBuilder
 *
 * @package daita\NcSmallPhpTools\Db
 */
class ExtendedQueryBuilder extends QueryBuilder {


	/** @var string */
	private $defaultSelectAlias;


	/**
	 * @param string $alias
	 *
	 * @return ExtendedQueryBuilder
	 */
	public function setDefaultSelectAlias(string $alias): ExtendedQueryBuilder {
		$this->defaultSelectAlias = $alias;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultSelectAlias(): string {
		return $this->defaultSelectAlias;
	}


	/**
	 * Limit the request to the Id
	 *
	 * @param int $id
	 *
	 * @return ExtendedQueryBuilder
	 */
	public function limitToId(int $id): ExtendedQueryBuilder {
		$this->limitToDBFieldInt('id', $id);

		return $this;
	}


	/**
	 * Limit the request to the Id (string)
	 *
	 * @param string $id
	 *
	 * @return ExtendedQueryBuilder
	 */
	public function limitToIdString(string $id): ExtendedQueryBuilder {
		$this->limitToDBField('id', $id, false);

		return $this;
	}


	/**
	 * Limit the request to the UserId
	 *
	 * @param string $userId
	 *
	 * @return ExtendedQueryBuilder
	 */
	public function limitToUserId(string $userId): ExtendedQueryBuilder {
		$this->limitToDBField('user_id', $userId, false);

		return $this;
	}


	/**
	 * Limit the request to the creation
	 *
	 * @param int $delay
	 *
	 * @return ExtendedQueryBuilder
	 * @throws Exception
	 */
	public function limitToCreation(int $delay = 0): ExtendedQueryBuilder {
		$date = new DateTime('now');
		$date->sub(new DateInterval('PT' . $delay . 'M'));

		$this->limitToDBFieldDateTime('creation', $date, true);

		return $this;
	}


	/**
	 * @param string $field
	 * @param string $value
	 * @param bool $cs - case sensitive
	 * @param string $alias
	 */
	public function limitToDBField(string $field, string $value, bool $cs = true, string $alias = ''
	): void {
		$expr = $this->exprLimitToDBField($field, $value, $cs, $alias);
		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param string $value
	 * @param bool $cs
	 * @param string $alias
	 *
	 * @return string
	 */
	protected function exprLimitToDBField(
		string $field, string $value, bool $cs = true, string $alias = ''
	): string {
		$expr = $this->expr();

		$pf = '';
		if ($this->getType() === DBALQueryBuilder::SELECT) {
			$pf = (($alias === '') ? $this->getDefaultSelectAlias() : $alias) . '.';
		}
		$field = $pf . $field;

		if ($cs) {
			return $expr->eq($field, $this->createNamedParameter($value));
		} else {
			$func = $this->func();

			return $expr->eq(
				$func->lower($field), $func->lower($this->createNamedParameter($value))
			);
		}
	}


	/**
	 * @param string $field
	 * @param int $value
	 * @param string $alias
	 */
	protected function limitToDBFieldInt(string $field, int $value, string $alias = ''
	) {
		$expr = $this->exprLimitToDBFieldInt($field, $value, $alias);
		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param int $value
	 * @param string $alias
	 *
	 * @return string
	 */
	protected function exprLimitToDBFieldInt(string $field, int $value, string $alias = ''
	): string {
		$expr = $this->expr();

		$pf = '';
		if ($this->getType() === DBALQueryBuilder::SELECT) {
			$pf = (($alias === '') ? $this->getDefaultSelectAlias() : $alias) . '.';
		}
		$field = $pf . $field;

		return $expr->eq($field, $this->createNamedParameter($value));
	}


	/**
	 * @param string $field
	 */
	protected function limitToDBFieldEmpty(string $field) {
		$expr = $this->expr();
		$pf =
			($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
															  . '.' : '';
		$field = $pf . $field;

		$this->andWhere($expr->eq($field, $this->createNamedParameter('')));
	}


	/**
	 * @param string $field
	 * @param DateTime $date
	 * @param bool $orNull
	 */
	protected function limitToDBFieldDateTime(string $field, DateTime $date, bool $orNull = false) {
		$expr = $this->expr();
		$pf =
			($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
															  . '.' : '';
		$field = $pf . $field;

		$orX = $expr->orX();
		$orX->add(
			$expr->lte($field, $this->createNamedParameter($date, IQueryBuilder::PARAM_DATE))
		);

		if ($orNull === true) {
			$orX->add($expr->isNull($field));
		}

		$this->andWhere($orX);
	}


	/**
	 * @param int $timestamp
	 * @param string $field
	 *
	 * @throws Exception
	 */
	protected function limitToSince(int $timestamp, string $field): void {
		$dTime = new DateTime();
		$dTime->setTimestamp($timestamp);

		$expr = $this->expr();
		$pf =
			($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
															  . '.' : '';
		$field = $pf . $field;

		$orX = $expr->orX();
		$orX->add(
			$expr->gte($field, $this->createNamedParameter($dTime, IQueryBuilder::PARAM_DATE))
		);

		$this->andWhere($orX);
	}


	/**
	 * @param string $field
	 * @param array $values
	 */
	protected function limitToDBFieldArray(string $field, array $values): void {
		$expr = $this->expr();
		$pf =
			($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
															  . '.' : '';
		$field = $pf . $field;

		if (!is_array($values)) {
			$values = [$values];
		}

		$orX = $expr->orX();
		foreach ($values as $value) {
			$orX->add($expr->eq($field, $this->createNamedParameter($value)));
		}

		$this->andWhere($orX);
	}


	/**
	 * @param string $field
	 * @param string $value
	 */
	protected function searchInDBField(string $field, string $value): void {
		$expr = $this->expr();

		$pf = ($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
																. '.' : '';
		$field = $pf . $field;

		$this->andWhere($expr->iLike($field, $this->createNamedParameter($value)));
	}

}

