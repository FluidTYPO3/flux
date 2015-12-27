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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Section
 */
class Section extends AbstractFormContainer implements ContainerInterface {

	/**
	 * @param array $settings
	 * @return \FluidTYPO3\Flux\Form\Container\Section
	 */
	public static function create(array $settings = array()) {
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
			'section' => '1',
			'el' => $this->buildChildren($this->children)
		);
		return $structureArray;
	}

}
