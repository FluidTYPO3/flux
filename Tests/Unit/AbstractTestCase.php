<?php
namespace FluidTYPO3\Flux\Tests\Unit;
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
 * ************************************************************* */

use FluidTYPO3\Flux\Form\Field\Custom;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

require_once ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Data/Xml.php');
require_once ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Data/Records.php');
require_once ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Classes/ContentController.php');
require_once ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Classes/DummyConfigurationProvider.php');
require_once ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Classes/DummyModel.php');
require_once ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Classes/DummyRepository.php');
require_once ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Classes/InvalidConfigurationProvider.php');


/**
 * @package Flux
 */
abstract class AbstractTestCase extends BaseTestCase {

	const FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL = 'EXT:flux/Tests/Fixtures/Templates/AbsolutelyMinimal.html';
	const FIXTURE_TEMPLATE_WITHOUTFORM = 'EXT:flux/Tests/Fixtures/Templates/WithoutForm.html';
	const FIXTURE_TEMPLATE_SHEETS = 'EXT:flux/Tests/Fixtures/Templates/Sheets.html';
	const FIXTURE_TEMPLATE_COMPACTED = 'EXT:flux/Tests/Fixtures/Templates/CompactToggledOn.html';
	const FIXTURE_TEMPLATE_USESPARTIAL = 'EXT:flux/Tests/Fixtures/Templates/UsesPartial.html';
	const FIXTURE_TEMPLATE_CUSTOM_SECTION = 'EXT:flux/Tests/Fixtures/Templates/CustomSection.html';
	const FIXTURE_TEMPLATE_PREVIEW_EMPTY = 'EXT:flux/Tests/Fixtures/Templates/EmptyPreview.html';
	const FIXTURE_TEMPLATE_BASICGRID = 'EXT:flux/Tests/Fixtures/Templates/BasicGrid.html';
	const FIXTURE_TEMPLATE_DUALGRID = 'EXT:flux/Tests/Fixtures/Templates/DualGrid.html';
	const FIXTURE_TEMPLATE_COLLIDINGGRID = 'EXT:flux/Tests/Fixtures/Templates/CollidingGrid.html';
	const FIXTURE_TYPOSCRIPT_DIR = 'EXT:flux/Tests/Fixtures/Data/TypoScript';

	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->objectManager = clone $objectManager;
		parent::__construct($name, $data, $dataName);
	}

	/**
	 * @param string $propertyName
	 * @param mixed $value
	 * @param mixed $expectedValue
	 * @param mixed $expectsChaining
	 * @return void
	 */
	protected function assertGetterAndSetterWorks($propertyName, $value, $expectedValue = NULL, $expectsChaining = FALSE) {
		$instance = $this->createInstance();
		$setter = 'set' . ucfirst($propertyName);
		$getter = 'get' . ucfirst($propertyName);
		$chained = $instance->$setter($value);
		if (TRUE === $expectsChaining) {
			$this->assertSame($instance, $chained);
		} else {
			$this->assertNull($chained);
		}
		$this->assertEquals($expectedValue, $instance->$getter());
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsArray($value) {
		$isArrayConstraint = new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
		$this->assertThat($value, $isArrayConstraint);
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsString($value) {
		$isStringConstraint = new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING);
		$this->assertThat($value, $isStringConstraint);
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsInteger($value) {
		$isIntegerConstraint = new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_INT);
		$this->assertThat($value, $isIntegerConstraint);
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsBoolean($value) {
		$isBooleanConstraint = new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_BOOL);
		$this->assertThat($value, $isBooleanConstraint);
	}

	/**
	 * @param mixed $value
	 */
	protected function assertIsValidAndWorkingFormObject($value) {
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $value);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $value);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\ContainerInterface', $value);
		/** @var Form $value */
		$structure = $value->build();
		$this->assertIsArray($structure);
		// scan for and attempt building of closures in structure
		foreach ($value->getFields() as $field) {
			if (TRUE === $field instanceof Custom) {
				$closure = $field->getClosure();
				$output = $closure($field->getArguments());
				$this->assertNotEmpty($output);
			}
		}
	}

	/**
	 * @param mixed $value
	 */
	protected function assertIsValidAndWorkingGridObject($value) {
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $value);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\ContainerInterface', $value);
		/** @var Form $value */
		$structure = $value->build();
		$this->assertIsArray($structure);
	}

	/**
	 * @param string $templateName
	 * @param array $variables
	 */
	protected function assertFluxTemplateLoadsWithoutErrors($templateName, $variables = array()) {
		if (0 === count($variables)) {
			$variables = array('row' => Records::$contentRecordWithoutParentAndWithoutChildren);
		}
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename($templateName);
		$service = $this->createFluxServiceInstance();
		$form = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', array(), 'Flux', $variables);
		if (NULL !== $form) {
			$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
			$this->assertIsArray($form->build());
		}
	}

	/**
	 * @return void
	 */
	public function truncateFluidCodeCache() {
		$files = glob(GeneralUtility::getFileAbsFileName('typo3temp/Cache/Code/fluid_template/*.php'));
		if (TRUE === is_array($files)) {
			foreach ($files as $file) {
				unlink($file);
			}
		}
	}

	/**
	 * @return string
	 */
	protected function getShorthandFixtureTemplatePathAndFilename() {
		return self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
	}

	/**
	 * @param string $shorthandTemplatePath
	 * @return string
	 */
	protected function getAbsoluteFixtureTemplatePathAndFilename($shorthandTemplatePath) {
		return GeneralUtility::getFileAbsFileName($shorthandTemplatePath);
	}

	/**
	 * @return FluxService
	 */
	protected function createFluxServiceInstance() {
		/** @var FluxService $fluxService */
		$fluxService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
		ObjectAccess::setProperty($fluxService, 'silent', TRUE, TRUE);
		return $fluxService;
	}

	/**
	 * @return object
	 */
	protected function createInstance() {
		$instance = $this->objectManager->get(substr(get_class($this), 0, -4));
		return $instance;
	}

	/**
	 * @param string $templatePathAndFilename
	 * @return array
	 */
	protected function performBasicTemplateReadTest($templatePathAndFilename) {
		$service = $this->createFluxServiceInstance();
		$form = $service->getFormFromTemplateFile($templatePathAndFilename);
		$this->assertIsValidAndWorkingFormObject($form);
		return $form;
	}

}
