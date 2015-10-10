<?php
namespace FluidTYPO3\Flux\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * Transforms data according to settings defined in the Form instance.
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
	protected function getObjectOfType($dataType, $uids) {
		$identifiers = TRUE === is_array($uids) ? $uids : GeneralUtility::trimExplode(',', trim($uids, ','), TRUE);
		$identifiers = array_map('intval', $identifiers);
		$isModel = $this->isDomainModelClassName($dataType);
		list ($container, $object) = FALSE !== strpos($dataType, '<') ? explode('<', trim($dataType, '>')) : array(NULL, $dataType);
		$repositoryClassName = $this->resolveRepositoryClassName($object);
		// Fast decisions
		if (TRUE === $isModel && NULL === $container) {
			if (TRUE === class_exists($repositoryClassName)) {
				$repository = $this->objectManager->get($repositoryClassName);
				return reset($this->loadObjectsFromRepository($repository, $identifiers));
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
	 * @param string $object
	 * @return string
	 */
	protected function resolveRepositoryClassName($object) {
		return str_replace('\\Domain\\Model\\', '\\Domain\\Repository\\', $object) . 'Repository';
	}

	/**
	 * @param string $dataType
	 * @return boolean
	 */
	protected function isDomainModelClassName($dataType) {
		return (FALSE !== strpos($dataType, '\\Domain\\Model\\'));
	}

	/**
	 * @param RepositoryInterface $repository
	 * @param array $identifiers
	 * @return mixed
	 */
	protected function loadObjectsFromRepository(RepositoryInterface $repository, array $identifiers) {
		return array_map(array($repository, 'findByUid'), $identifiers);
	}

}
