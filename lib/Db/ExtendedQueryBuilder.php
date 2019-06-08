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


use daita\NcSmallPhpTools\IExtendedQueryBuilder;
use DateInterval;
use DateTime;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Exception;
use OC\DB\QueryBuilder\QueryBuilder;
use OCP\DB\QueryBuilder\ICompositeExpression;
use OCP\DB\QueryBuilder\IQueryBuilder;


/**
 * Class RequestQueryBuilder
 *
 * @package daita\NcSmallPhpTools\Db
 */
class ExtendedQueryBuilder extends QueryBuilder implements IExtendedQueryBuilder {


	/** @var string */
	private $defaultSelectAlias;


	/**
	 * @param string $alias
	 *
	 * @return IExtendedQueryBuilder
	 */
	public function setDefaultSelectAlias(string $alias): IExtendedQueryBuilder {
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
	public function limitToId(int $id): IExtendedQueryBuilder {
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
	public function limitToIdString(string $id): IExtendedQueryBuilder {
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
	public function limitToUserId(string $userId): IExtendedQueryBuilder {
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
	public function limitToCreation(int $delay = 0): IExtendedQueryBuilder {
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
	public function limitToDBField(
		string $field, string $value, bool $cs = true, string $alias = ''
	): void {
		$expr = $this->exprLimitToDBField($field, $value, true, $cs, $alias);

		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param string $value
	 * @param bool $cs - case sensitive
	 * @param string $alias
	 */
	public function filterDBField(string $field, string $value, bool $cs = true, string $alias = ''
	): void {
		$expr = $this->exprLimitToDBField($field, $value, false, $cs, $alias);
		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param string $value
	 * @param bool $eq
	 * @param bool $cs
	 * @param string $alias
	 *
	 * @return string
	 */
	public function exprLimitToDBField(
		string $field, string $value, bool $eq = true, bool $cs = true, string $alias = ''
	): string {
		$expr = $this->expr();

		$pf = '';
		if ($this->getType() === DBALQueryBuilder::SELECT) {
			$pf = (($alias === '') ? $this->getDefaultSelectAlias() : $alias) . '.';
		}
		$field = $pf . $field;

		$comp = 'eq';
		if ($eq === false) {
			$comp = 'neq';
		}

		if ($cs) {
			return $expr->$comp($field, $this->createNamedParameter($value));
		} else {
			$func = $this->func();

			return $expr->$comp(
				$func->lower($field), $func->lower($this->createNamedParameter($value))
			);
		}
	}


	/**
	 * @param string $field
	 * @param array $values
	 * @param bool $cs - case sensitive
	 * @param string $alias
	 */
	public function limitToDBFieldArray(
		string $field, array $values, bool $cs = true, string $alias = ''
	): void {
		$expr = $this->exprLimitToDBFieldArray($field, $values, true, $cs, $alias);
		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param string $value
	 * @param bool $cs - case sensitive
	 * @param string $alias
	 */
	public function filterDBFieldArray(
		string $field, string $value, bool $cs = true, string $alias = ''
	): void {
		$expr = $this->exprLimitToDBField($field, $value, false, $cs, $alias);
		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param array $values
	 * @param bool $eq
	 * @param bool $cs
	 * @param string $alias
	 *
	 * @return ICompositeExpression
	 */
	public function exprLimitToDBFieldArray(
		string $field, array $values, bool $eq = true, bool $cs = true, string $alias = ''
	): ICompositeExpression {
		$expr = $this->expr();

		$pf = '';
		if ($this->getType() === DBALQueryBuilder::SELECT) {
			$pf = (($alias === '') ? $this->getDefaultSelectAlias() : $alias) . '.';
		}
		$field = $pf . $field;

		$func = $this->func();
		if ($eq === false) {
			$comp = 'neq';
			$junc = $expr->andX();
		} else {
			$comp = 'eq';
			$junc = $expr->orX();
		}

		foreach ($values as $value) {
			if ($cs) {
				$junc->add($expr->$comp($field, $this->createNamedParameter($value)));
			} else {
				$junc->add(
					$expr->$comp(
						$func->lower($field), $func->lower($this->createNamedParameter($value))
					)
				);
			}
		}

		return $junc;
	}


	/**
	 * @param string $field
	 * @param int $value
	 * @param string $alias
	 */
	public function limitToDBFieldInt(string $field, int $value, string $alias = ''): void {
		$expr = $this->exprLimitToDBFieldInt($field, $value, $alias, true);
		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param int $value
	 * @param string $alias
	 */
	public function filterDBFieldInt(string $field, int $value, string $alias = ''): void {
		$expr = $this->exprLimitToDBFieldInt($field, $value, $alias, false);
		$this->andWhere($expr);
	}


	/**
	 * @param string $field
	 * @param int $value
	 * @param string $alias
	 * @param bool $eq
	 *
	 * @return string
	 */
	public function exprLimitToDBFieldInt(
		string $field, int $value, string $alias = '', bool $eq = true
	): string {
		$expr = $this->expr();

		$pf = '';
		if ($this->getType() === DBALQueryBuilder::SELECT) {
			$pf = (($alias === '') ? $this->getDefaultSelectAlias() : $alias) . '.';
		}
		$field = $pf . $field;

		$comp = 'eq';
		if ($eq === false) {
			$comp = 'neq';
		}

		return $expr->$comp($field, $this->createNamedParameter($value));
	}


	/**
	 * @param string $field
	 */
	public function limitToDBFieldEmpty(string $field): void {
		$expr = $this->expr();
		$pf =
			($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
															  . '.' : '';
		$field = $pf . $field;

		$this->andWhere($expr->eq($field, $this->createNamedParameter('')));
	}


	/**
	 * @param string $field
	 */
	public function filterDBFieldEmpty(string $field): void {
		$expr = $this->expr();
		$pf =
			($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
															  . '.' : '';
		$field = $pf . $field;

		$this->andWhere($expr->neq($field, $this->createNamedParameter('')));
	}


	/**
	 * @param string $field
	 * @param DateTime $date
	 * @param bool $orNull
	 */
	public function limitToDBFieldDateTime(string $field, DateTime $date, bool $orNull = false
	): void {
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
	public function limitToSince(int $timestamp, string $field): void {
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
	 * @param string $value
	 */
	public function searchInDBField(string $field, string $value): void {
		$expr = $this->expr();

		$pf = ($this->getType() === DBALQueryBuilder::SELECT) ? $this->getDefaultSelectAlias()
																. '.' : '';
		$field = $pf . $field;

		$this->andWhere($expr->iLike($field, $this->createNamedParameter($value)));
	}

}

