<?php
namespace FluidTYPO3\Flux\Transformation;
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * Transforms data according to settings defined in the Form instance.
 *
 * @package FluidTYPO3\Flux
 */
class FormDataTransformer {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Transforms members on $values recursively according to the provided
	 * Flux configuration extracted from a Flux template. Uses "transform"
	 * attributes on fields to determine how to transform values.
	 *
	 * @param array $values
	 * @param Form $form
	 * @param string $prefix
	 * @return array
	 */
	public function transformAccordingToConfiguration($values, Form $form, $prefix = '') {
		foreach ((array) $values as $index => $value) {
			if (TRUE === is_array($value)) {
				$value = $this->transformAccordingToConfiguration($value, $form, $prefix . $index . '.');
			} else {
				/** @var FieldInterface|ContainerInterface $object */
				$object = $form->get($prefix . $index, TRUE);
				if (FALSE !== $object) {
					$transformType = $object->getTransform();
					$value = $this->transformValueToType($value, $transformType);
				}
			}
			$values[$index] = $value;
		}
		return $values;
	}

	/**
	 * Transforms a single value to $dataType
	 *
	 * @param string $value
	 * @param string $dataType
	 * @return mixed
	 */
	protected function transformValueToType($value, $dataType) {
		if ('int' === $dataType || 'integer' === $dataType) {
			return intval($value);
		} elseif ('float' === $dataType) {
			return floatval($value);
		} elseif ('array' === $dataType) {
			return explode(',', $value);
		} else {
			return $this->getObjectOfType($dataType, $value);
		}
	}

	/**
	 * Gets a DomainObject or QueryResult of $dataType
	 *
	 * @param string $dataType
	 * @param string $uids
	 * @return mixed
	 */
	private function getObjectOfType($dataType, $uids) {
		$identifiers = TRUE === is_array($uids) ? $uids : GeneralUtility::trimExplode(',', trim($uids, ','), TRUE);
		$identifiers = array_map('intval', $identifiers);
		$isModel = (FALSE !== strpos($dataType, '_Domain_Model_') || FALSE !== strpos($dataType, '\\Domain\\Model\\'));
		list ($container, $object) = FALSE !== strpos($dataType, '<') ? explode('<', trim($dataType, '>')) : array(NULL, $dataType);
		$repositoryClassName = str_replace('_Domain_Model_', '_Domain_Repository_', str_replace('\\Domain\\Model\\', '\\Domain\\Repository\\', $object)) . 'Repository';
		// Fast decisions
		if (TRUE === $isModel && NULL === $container) {
			if (TRUE === class_exists($repositoryClassName)) {
				$repository = $this->objectManager->get($repositoryClassName);
				$uid = array_pop($identifiers);
				return $repository->findOneByUid($uid);
			}
		} elseif (TRUE === class_exists($dataType)) {
			// using constructor value to support objects like DateTime
			return $this->objectManager->get($dataType, $uids);
		}
		// slower decisions with support for type-hinted collection objects
		if ($container && $object) {
			if (TRUE === $isModel && TRUE === class_exists($repositoryClassName) && 0 < count($identifiers)) {
				/** @var $repository RepositoryInterface */
				$repository = $this->objectManager->get($repositoryClassName);
				return $this->loadObjectsFromRepository($repository, $identifiers);
			} else {
				$container = $this->objectManager->get($container);
				return $container;
			}
		}
		return $uids;
	}

	/**
	 * @param RepositoryInterface $repository
	 * @param array $identifiers
	 * @return mixed
	 */
	private function loadObjectsFromRepository(RepositoryInterface $repository, $identifiers) {
		if (TRUE === method_exists($repository, 'findByIdentifiers')) {
			return $repository->findByIdentifiers($identifiers);
		} else {
			$query = $repository->createQuery();
			$query->matching($query->in('uid', $identifiers));
			return $query->execute();
		}
	}

}
