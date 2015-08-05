<?php
namespace FluidTYPO3\Flux\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Sheet extends AbstractFormContainer implements ContainerInterface, FieldContainerInterface {

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $shortDescription;

	/**
	 * @param string $shortDescription
	 * @return self
	 */
	public function setShortDescription($shortDescription) {
		$this->shortDescription = $shortDescription;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getShortDescription() {
		return $this->resolveLocalLanguageValueOfLabel($this->shortDescription, $this->getPath() . '.shortDescription');
	}

	/**
	 * @param string $description
	 * @return self
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->resolveLocalLanguageValueOfLabel($this->description, $this->getPath() . '.description');
	}

	/**
	 * @return array
	 */
	public function build() {
		$sheetStructArray = [
			'ROOT' => [
				'TCEforms' => [
					'sheetTitle' => $this->getLabel(),
					'sheetDescription' => $this->getDescription(),
					'sheetShortDescr' => $this->getShortDescription()
				],
				'type' => 'array',
				'el' => $this->buildChildren($this->children)
			]
		];
		return $sheetStructArray;
	}

	/**
	 * @return array
	 */
	protected function buildChildren() {
		$structure = [];
		/** @var \FluidTYPO3\Flux\Form\FormInterface[] $children */
		$children = $this->getFields();
		foreach ($children as $child) {
			$name = $child->getName();
			$structure[$name] = $child->build();
		}
		return $structure;
	}

	/**
	 * @return \FluidTYPO3\Flux\Form\FieldInterface[]
	 */
	public function getFields() {
		$fields = [];
		foreach ($this->children as $child) {
			if (TRUE === $child->getEnabled()) {
				$isSectionOrContainer = (TRUE === $child instanceof Section || TRUE === $child instanceof Container);
				$isFieldEmulatorAndHasChildren = ($isSectionOrContainer && TRUE === $child->hasChildren());
				$isActualField = (TRUE === $child instanceof FieldInterface);
				$isNotInsideObject = (FALSE === $child->isChildOfType('Object'));
				$isNotInsideContainer = (FALSE === $child->isChildOfType('Container'));
				if (TRUE === $isFieldEmulatorAndHasChildren || (TRUE === $isActualField && TRUE === $isNotInsideObject && TRUE === $isNotInsideContainer)) {
					$name = $child->getName();
					$fields[$name] = $child;
				}
			}
		}
		return $fields;
	}

}
