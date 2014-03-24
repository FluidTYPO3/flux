<?php
namespace FluidTYPO3\Flux;
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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * @package Flux
 */
class FormTest extends AbstractTestCase {

	/**
	 * @return Form
	 */
	protected function getEmptyDummyForm() {
		/** @var Form $form */
		$form = $this->objectManager->get('FluidTYPO3\Flux\Form');
		return $form;
	}

	/**
	 * @param string $template
	 * @return Form
	 */
	protected function getDummyFormFromTemplate($template = self::FIXTURE_TEMPLATE_BASICGRID) {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename($template);
		$service = $this->createFluxServiceInstance();
		$form = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', array(), 'flux');
		return $form;
	}

	/**
	 * @test
	 */
	public function canReturnFormObjectWithoutFormPresentInTemplate() {
		$form = $this->getDummyFormFromTemplate(self::FIXTURE_TEMPLATE_WITHOUTFORM);
		$this->assertIsValidAndWorkingFormObject($form);
	}

	/**
	 * @test
	 */
	public function canRetrieveStoredForm() {
		$form = $this->getDummyFormFromTemplate();
		$this->assertIsValidAndWorkingFormObject($form);
	}

	/**
	 * @test
	 */
	public function canUseIdProperty() {
		$form = $this->getDummyFormFromTemplate();
		$id = 'dummyId';
		$form->setId($id);
		$this->assertSame($id, $form->getId());
	}

	/**
	 * @test
	 */
	public function canUseEnabledProperty() {
		$form = $this->getDummyFormFromTemplate();
		$form->setEnabled(FALSE);
		$this->assertSame(FALSE, $form->getEnabled());
	}

	/**
	 * @test
	 */
	public function canUseGroupProperty() {
		$form = $this->getDummyFormFromTemplate();
		$group = 'dummyGroup';
		$form->setGroup($group);
		$this->assertSame($group, $form->getGroup());
	}

	/**
	 * @test
	 */
	public function canUseExtensionNameProperty() {
		$form = $this->getDummyFormFromTemplate();
		$extensionName = 'flux';
		$form->setExtensionName($extensionName);
		$this->assertSame($extensionName, $form->getExtensionName());
	}

	/**
	 * @test
	 */
	public function canUseIconPropertyAndTransformToAbsolutePath() {
		$form = $this->getDummyFormFromTemplate();
		$icon = 'EXT:flux/ext_icon.gif';
		$form->setIcon($icon);
		$this->assertSame(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($icon), $form->getIcon());
	}

	/**
	 * @test
	 */
	public function canUseDescriptionProperty() {
		$form = $this->getDummyFormFromTemplate();
		$description = 'This is a dummy description';
		$form->setDescription($description);
		$this->assertSame($description, $form->getDescription());
	}

	/**
	 * @test
	 */
	public function canUseDescriptionPropertyAndReturnLanguageLabelWhenDescriptionEmpty() {
		$form = $this->getDummyFormFromTemplate();
		$form->setDescription(NULL);
		$this->assertNotNull($form->getDescription());
	}

	/**
	 * @test
	 */
	public function canAddSameFieldTwiceWithoutErrorAndWithoutDoubles() {
		$form = $this->getEmptyDummyForm();
		$field = $form->createField('Input', 'input', 'Input field');
		$form->last()->add($field)->add($field);
		$this->assertTrue($form->last()->has($field));
	}

	/**
	 * @test
	 */
	public function canAddSameContainerTwiceWithoutErrorAndWithoutDoubles() {
		$form = $this->getEmptyDummyForm();
		$sheet = $form->createContainer('Sheet', 'sheet', 'Sheet object');
		$form->add($sheet)->add($sheet);
		$this->assertTrue($form->has($sheet));
	}

	/**
	 * @test
	 */
	public function canGetLabelFromVariousObjectsInsideForm() {
		$form = $this->getEmptyDummyForm();
		$field = $form->createField('Input', 'test');
		$objectField = $form->createField('Input', 'objectField');
		$form->add($field);
		$section = $form->createContainer('Section', 'section');
		$object = $form->createContainer('Object', 'object');
		$object->add($objectField);
		$section->add($object);
		$form->add($section);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $object->getRoot());
		$this->assertNotEmpty($form->get('options')->getLabel());
		$this->assertNotEmpty($form->get('test', TRUE)->getLabel());
		$this->assertNotEmpty($form->get('object', TRUE)->getLabel());
		$this->assertNotEmpty($form->get('objectField', TRUE)->getLabel());
	}

	/**
	 * @test
	 */
	public function canAddMultipleFieldsToContainer() {
		$form = $this->getEmptyDummyForm();
		$fields = array(
			$form->createField('Input', 'test1'),
			$form->createField('Input', 'test2'),
		);
		$form->addAll($fields);
		$this->assertTrue($form->last()->has($fields[0]));
		$this->assertTrue($form->last()->has($fields[1]));
	}

	/**
	 * @test
	 */
	public function canRemoveFieldFromContainerByName() {
		$form = $this->getEmptyDummyForm();
		$field = $form->createField('Input', 'test');
		$form->add($field);
		$form->last()->remove('test');
		$this->assertFalse($form->last()->has('test'));
	}

	/**
	 * @test
	 */
	public function canRemoveFieldFromContainerByInstance() {
		$form = $this->getEmptyDummyForm();
		$field = $form->createField('Input', 'test');
		$form->add($field);
		$form->last()->remove($field);
		$this->assertFalse($form->last()->has('test'));
	}

	/**
	 * @test
	 */
	public function canRemoveBadFieldByNameWithoutErrorAndReturnFalse() {
		$form = $this->getEmptyDummyForm();
		$this->assertFalse($form->last()->remove('test'));
	}

	/**
	 * @test
	 */
	public function canRemoveBadFieldByInstanceWithoutErrorAndReturnFalse() {
		$form = $this->getEmptyDummyForm();
		$field = \FluidTYPO3\Flux\Form\Field\Input::create(array('type' => 'Input', 'name' => 'badname'));
		$child = $form->last()->remove($field);
		$this->assertFalse($child);
	}

	/**
	 * @test
	 */
	public function canCreateAndAddField() {
		$form = $this->getEmptyDummyForm();
		$field = $form->createField('Input', 'input');
		$form->add($field);
		$this->assertIsValidAndWorkingFormObject($form);
		$this->assertTrue($form->last()->has('input'));
	}

	/**
	 * @test
	 */
	public function canCreateAndAddContainer() {
		$form = $this->getEmptyDummyForm();
		$container = $form->createContainer('Section', 'section');
		$form->add($container);
		$this->assertTrue($form->last()->has('section'));
		$this->assertIsValidAndWorkingFormObject($form);
	}

	/**
	 * @test
	 */
	public function canCreateAndAddWizard() {
		$form = $this->getEmptyDummyForm();
		$field = $form->createField('Input', 'input');
		$wizard = $form->createWizard('Add', 'add');
		$field->add($wizard);
		$form->add($field);
		$this->assertIsValidAndWorkingFormObject($form);
	}

	/**
	 * @test
	 */
	public function supportsFormComponentsPlacedInPartialTemplates() {
		$template = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_USESPARTIAL);
		$service = $this->createFluxServiceInstance();
		$paths = array(
			'templateRootPath' => 'EXT:flux/Tests/Fixtures/Templates',
			'partialRootPath' => 'EXT:flux/Tests/Fixtures/Partials'
		);
		$form = $service->getFormFromTemplateFile($template, 'Configuration', 'form', $paths);
		$this->assertIsValidAndWorkingFormObject($form);
	}

	/**
	 * @test
	 */
	public function canCreateFromDefinition() {
		$properties = array(
			'name' => 'test',
			'label' => 'Test field'
		);
		$instance = Form::create($properties);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $instance);
	}

	/**
	 * @test
	 */
	public function canCreateFromDefinitionWithSheets() {
		$properties = array(
			'name' => 'test',
			'label' => 'Test field',
			'sheets' => array(
				'sheet' => array(
					'fields' => array()
				),
				'anotherSheet' => array(
					'fields' => array()
				),
			)
		);
		$instance = Form::create($properties);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $instance);
	}

	/**
	 * @test
	 */
	public function canDetermineHasChildrenFalse() {
		$instance = Form::create();
		$this->assertFalse($instance->hasChildren());
	}

	/**
	 * @test
	 */
	public function canDetermineHasChildrenTrue() {
		$instance = Form::create();
		$instance->createField('Input', 'test');
		$this->assertTrue($instance->hasChildren());
	}

	/**
	 * @test
	 */
	public function canSetAndGetOptions() {
		$instance = Form::create();
		$instance->setOption('test', 'testing');
		$this->assertSame('testing', $instance->getOption('test'));
		$this->assertIsArray($instance->getOptions());
		$this->assertArrayHasKey('test', $instance->getOptions());
		$options = array('foo' => 'bar');
		$instance->setOptions($options);
		$this->assertSame('bar', $instance->getOption('foo'));
		$this->assertArrayHasKey('foo', $instance->getOptions());
		$this->assertArrayNotHasKey('test', $instance->getOptions());
	}

	/**
	 * @test
	 */
	public function canSetAndGetOutlet() {
		/** @var \FluidTYPO3\Flux\Outlet\StandardOutlet $outlet */
		$outlet = $this->getMock('FluidTYPO3\Flux\Outlet\StandardOutlet');
		$form = Form::create();
		$form->setOutlet($outlet);
		$this->assertSame($outlet, $form->getOutlet());
	}

	/**
	 * @test
	 */
	public function dispatchesDebugMessageOnProblematicId() {
		$service = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('message'));
		$service->expects($this->once())->method('message');
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Form');
		ObjectAccess::setProperty($instance, 'configurationService', $service, TRUE);
		$instance->setId('I-am-not-valid');
	}

}
