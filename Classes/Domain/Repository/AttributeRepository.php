<?php
namespace FluidTYPO3\Flux\Domain\Repository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalAnd;
use TYPO3\CMS\Extbase\Persistence\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Attribute Repository
 *
 * Extbase Repository implementation of the Flux Attributes
 * assigned to tables using TCA.
 *
 * @package Flux
 */
class AttributeRepository extends Repository {

	/**
	 * @param array $record
	 * @param string $table
	 * @param string $field
	 * @return QueryInterface
	 */
	protected function prepareQueryBasedOnRecordAndTableAndOptionalField(array $record, $table, $field) {
		$query = $this->createQuery();
		if (TRUE === isset($record['pid'])) {
			$query->getQuerySettings()->setStoragePageIds(array($record['pid']));
		} else {
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
		}
		$constraints = array();
		if (TRUE === isset($record['uid'])) {
			array_push($constraints, $query->equals('for_identity', $record['uid']));
		}
		if (NULL !== $field) {
			array_push($constraints, $query->equals('for_field', $field));
		}
		array_push($constraints, $query->equals('for_table', $table));
		$query->matching($query->logicalAnd($constraints));
		return $query;
	}

	/**
	 * @param string $name
	 * @param array $record
	 * @param string $table
	 * @param string $field
	 * @return Attribute
	 */
	public function findByNameForRecordFromTableWithOptionalField($name, array $record, $table, $field = NULL) {
		$query = $this->prepareQueryBasedOnRecordAndTableAndOptionalField($record, $table, $field);
		/** @var LogicalAnd $constraint */
		$constraint = $query->logicalAnd(array($query->getConstraint()->getConstraint1(), $query->equals('name', $name)));
		$query->matching($constraint);
		return $query->execute()->getFirst();
	}

	/**
	 * @param array $record
	 * @param string $table
	 * @param string $field Optional field name to also include in query clause
	 * @return QueryResult
	 */
	public function findAllForRecordFromTableWithOptionalField(array $record, $table, $field = NULL) {
		$query = $this->prepareQueryBasedOnRecordAndTableAndOptionalField($record, $table, $field);
		return $query->execute();
	}

}
