<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
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

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field\AbstractFieldViewHelperTestCase;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

/**
 * @package Flux
 */
class ControllerActionsViewHelperTest extends AbstractFieldViewHelperTestCase {

	/**
	 * @var array
	 */
	protected $defaultArguments = array(
		'label' => 'Test field',
		'extensionName' => '',
		'pluginName' => 'Flux',
		'controllerName' => 'Content',
		'actions' => array(),
		'disableLocalLanguageLabels' => FALSE,
		'excludeActions' => array(),
		'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
		'prefixOnRequiredArguments' => '*',
		'subActions' => array()
	);

	/**
	 * @test
	 */
	public function acceptsTraversableListOfActions() {
		$array = array('foo', 'bar');
		$traversable = new \ArrayIterator($array);
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
		$request = new Request();
		$request->setControllerExtensionName('Flux');
		$request->setControllerVendorName('FluidTYPO3');
		$expected = 'FluidTYPO3.Flux';
		$result = $this->callInaccessibleMethod($instance, 'getFullExtensionNameFromRequest', $request);
		$this->assertEquals($expected, $result);
	}

}
