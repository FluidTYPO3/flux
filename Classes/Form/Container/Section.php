<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Tx_Flux_Form_Container_Section extends Tx_Flux_Form_AbstractFormContainer implements Tx_Flux_Form_ContainerInterface {

	/**
	 * @param array $settings
	 * @return Tx_Flux_Form_Container_Section
	 */
	public static function create(array $settings) {
		/** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		/** @var Tx_Flux_Form_Container_Section */
		$section = $objectManager->get('Tx_Flux_Form_Container_Section');
		foreach ($settings as $settingName => $settingValue) {
			$setterMethodName = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::buildSetterMethodName($settingName);
			if (TRUE === method_exists($section, $setterMethodName)) {
				\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($section, $settingName, $settingValue);
			}
		}
		if (TRUE === isset($settings['objects'])) {
			foreach ($settings['objects'] as $fieldName => $objectSettings) {
				if (FALSE === isset($objectSettings['name'])) {
					$objectSettings['name'] = $fieldName;
				}
				$object = Tx_Flux_Form_Container_Object::create($objectSettings);
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
			'section' => 1,
			'el' => $this->buildChildren()
		);
		return $structureArray;
	}

}
