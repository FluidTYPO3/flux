<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Tests_Functional_FormTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @return Tx_Flux_Form
	 */
	protected function getEmptyDummyForm() {
		/** @var Tx_Flux_Form $form */
		$form = $this->objectManager->get('Tx_Flux_Form');
		return $form;
	}

	/**
	 * @return Tx_Flux_Form
	 */
	protected function getDummyFormFromTemplate() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$form = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', array(), 'flux');
		return $form;
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
		$this->assertSame(t3lib_div::getFileAbsFileName($icon), $form->getIcon());
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
		$description = NULL;
		$form->setDescription($description);
		$this->assertNotSame($description, $form->getDescription());
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

}
