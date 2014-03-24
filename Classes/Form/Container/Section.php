<?php
namespace FluidTYPO3\Flux\Form\Container;
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

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\Container\Object;
use FluidTYPO3\Flux\Form\ContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Section extends AbstractFormContainer implements ContainerInterface {

	/**
	 * @param array $settings
	 * @return FluidTYPO3\Flux\Form\Container\Section
	 */
	public static function create(array $settings) {
		/** @var ObjectManagerInterface $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var Section */
		$section = $objectManager->get('FluidTYPO3\Flux\Form\Container\Section');
		foreach ($settings as $settingName => $settingValue) {
			$setterMethodName = ObjectAccess::buildSetterMethodName($settingName);
			if (TRUE === method_exists($section, $setterMethodName)) {
				ObjectAccess::setProperty($section, $settingName, $settingValue);
			}
		}
		if (TRUE === isset($settings['objects'])) {
			foreach ($settings['objects'] as $fieldName => $objectSettings) {
				if (FALSE === isset($objectSettings['name'])) {
					$objectSettings['name'] = $fieldName;
				}
				$object = Object::create($objectSettings);
				$section->add($object);
			}
		}
		return $section;
	}

	/**
	 * @return array
	 */
	public function build() {
		$structureArray = array(
			'type' => 'array',
			'title' => $this->getLabel(),
			'section' => 1,
			'el' => $this->buildChildren()
		);
		return $structureArray;
	}

}
