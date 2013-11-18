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
class Tx_Flux_ViewHelpers_Flexform_Field_ControllerActionsViewHelperTest extends Tx_Flux_ViewHelpers_AbstractViewHelperTest {

	/**
	 * @test
	 */
	public function acceptsTraversableListOfActions() {
		$array = array('foo', 'bar');
		$traversable = new ArrayIterator($array);
		$arguments = array(
			'label' => 'Test field',
			'extensionName' => 'Flux',
			'pluginName' => 'API',
			'controllerName' => 'Flux',
			'actions' => $traversable,
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => array(),
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => array()
		);
		$instance = $this->buildViewHelperInstance($arguments);
		$component = $instance->getComponent();
		$this->assertSame($array, $component->getActions());
	}

	/**
	 * @test
	 */
	public function throwsExceptionOnInvalidExtensionPluginNameAndActionsCombination() {
		$arguments = array(
			'label' => 'Test field',
			'extensionName' => '',
			'pluginName' => '',
			'controllerName' => '',
			'actions' => array(),
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => array(),
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => array()
		);
		$instance = $this->buildViewHelperInstance($arguments, array(), NULL, $arguments['extensionName'], $arguments['pluginName']);;
		$this->setExpectedException('RuntimeException', NULL, 1346514748);
		$instance->initializeArgumentsAndRender();
	}
	/**
	 * @test
	 */
	public function supportsUseOfControllerAndActionSeparator() {
		$arguments = array(
			'label' => 'Test field',
			'extensionName' => 'Flux',
			'pluginName' => 'API',
			'controllerName' => 'Flux',
			'actions' => array(),
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => array(),
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => array(),
			'separator' => ' :: '
		);
		$instance = $this->buildViewHelperInstance($arguments, array(), NULL, $arguments['extensionName'], $arguments['pluginName']);;
		$instance->initializeArgumentsAndRender();
		$component = $instance->getComponent();
		$this->assertSame($arguments['separator'], $component->getSeparator());
	}

	/**
	 * @test
	 */
	public function canGetCombinedExtensionKeyFromRequest() {
		$arguments = array(
			'label' => 'Test field',
			'pluginName' => 'API',
			'controllerName' => 'Flux',
			'actions' => array(),
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => array(),
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => array(),
			'separator' => ' :: '
		);
		$instance = $this->buildViewHelperInstance($arguments);
		$request = new \TYPO3\CMS\Extbase\Mvc\Web\Request();
		$request->setControllerExtensionName('Flux');
		$request->setControllerVendorName('FluidTYPO3');
		$expected = 'FluidTYPO3.Flux';
		$result = $this->callInaccessibleMethod($instance, 'getFullExtensionNameFromRequest', $request);
		$this->assertEquals($expected, $result);
	}

}
