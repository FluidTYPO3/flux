<?php
namespace FluidTYPO3\Flux\Form;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 *****************************************************************/

use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\WizardInterface;

/**
 * @package Flux
 * @subpackage Form
 */
abstract class AbstractFormContainer extends AbstractFormComponent implements ContainerInterface {

	/**
	 * @var \SplObjectStorage
	 */
	protected $children;

	/**
	 * @var string
	 */
	protected $transform;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->children = new \SplObjectStorage();
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param null $label
	 * @return FieldInterface
	 */
	public function createField($type, $name, $label = NULL) {
		$field = parent::createField($type, $name, $label);
		$this->add($field);
		return $field;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param null $label
	 * @return ContainerInterface
	 */
	public function createContainer($type, $name, $label = NULL) {
		$container = parent::createContainer($type, $name, $label);
		$this->add($container);
		return $container;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param null $label
	 * @return WizardInterface
	 */
	public function createWizard($type, $name, $label = NULL) {
		$wizard = parent::createWizard($type, $name, $label);
		$this->add($wizard);
		return $wizard;
	}

	/**
	 * @param FormInterface $child
	 * @return FormInterface
	 */
	public function add(FormInterface $child) {
		if (FALSE === $this->children->contains($child)) {
			$this->children->attach($child);
			$child->setParent($this);
		}
		return $this;
	}

	/**
	 * @param array|Traversable $children
	 * @return FormInterface
	 */
	public function addAll($children) {
		foreach ($children as $child) {
			$this->add($child);
		}
		return $this;
	}

	/**
	 * @param string $childName
	 * @return FormInterface|FALSE
	 */
	public function remove($childName) {
		foreach ($this->children as $child) {
			$isMatchingInstance = (TRUE === $childName instanceof FormInterface && $childName->getName() === $child->getName());
			$isMatchingName = ($childName === $child->getName());
			if (TRUE === $isMatchingName || TRUE === $isMatchingInstance) {
				$this->children->detach($child);
				$this->children->rewind();
				$child->setParent(NULL);
				return $child;
			}
		}
		return FALSE;
	}

	/**
	 * @param mixed $childOrChildName
	 * @return boolean
	 */
	public function has($childOrChildName) {
		if (TRUE === $childOrChildName instanceof FormInterface) {
			$name = $childOrChildName->getName();
		} else {
			$name = $childOrChildName;
		}
		return (FALSE !== $this->get($name));
	}

	/**
	 * @param string $childName
	 * @param boolean $recursive
	 * @param string $requiredClass
	 * @return FormInterface|FALSE
	 */
	public function get($childName, $recursive = FALSE, $requiredClass = NULL) {
		foreach ($this->children as $existingChild) {
			if ($childName === $existingChild->getName() && ($requiredClass === NULL || TRUE === $existingChild instanceof $requiredClass)) {
				return $existingChild;
			}
			if (TRUE === $recursive && TRUE === $existingChild instanceof ContainerInterface) {
				$candidate = $existingChild->get($childName, $recursive);
				if (FALSE !== $candidate) {
					return $candidate;
				}
			}
		}
		return FALSE;
	}

	/**
	 * @return FormInterface|FALSE
	 */
	public function last() {
		$result = array_pop(iterator_to_array($this->children));
		return $result;
	}

	/**
	 * @return array
	 */
	protected function buildChildren() {
		$structure = array();
		/** @var FormInterface[] $children */
		$children = $this->children;
		foreach ($children as $child) {
			$name = $child->getName();
			$structure[$name] = $child->build();
		}
		return $structure;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		return 0 < $this->children->count();
	}

	/**
	 * @param string $transform
	 * @return ContainerInterface
	 */
	public function setTransform($transform) {
		$this->transform = $transform;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTransform() {
		return $this->transform;
	}

}
