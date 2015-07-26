<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field\AbstractFieldViewHelperTestCase;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

/**
 * @package Flux
 */
class ControllerActionsViewHelperTest extends AbstractFieldViewHelperTestCase {

	/**
	 * @var array
	 */
	protected $defaultArguments = [
		'label' => 'Test field',
		'controllerExtensionName' => '',
		'pluginName' => 'Flux',
		'controllerName' => 'Content',
		'actions' => [],
		'disableLocalLanguageLabels' => FALSE,
		'excludeActions' => [],
		'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
		'prefixOnRequiredArguments' => '*',
		'subActions' => []
	];

	/**
	 * @test
	 */
	public function acceptsTraversableListOfActions() {
		$array = ['foo', 'bar'];
		$traversable = new \ArrayIterator($array);
		$arguments = [
			'label' => 'Test field',
			'controllerExtensionName' => 'Flux',
			'pluginName' => 'API',
			'controllerName' => 'Flux',
			'actions' => $traversable,
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => [],
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => []
		];
		$instance = $this->buildViewHelperInstance($arguments);
		$component = $instance->getComponent();
		$this->assertSame($array, $component->getActions());
	}

	/**
	 * @test
	 */
	public function throwsExceptionOnInvalidExtensionPluginNameAndActionsCombination() {
		$arguments = [
			'label' => 'Test field',
			'controllerExtensionName' => '',
			'pluginName' => '',
			'controllerName' => '',
			'actions' => [],
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => [],
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => []
		];
		$instance = $this->buildViewHelperInstance($arguments, [], NULL, $arguments['extensionName'], $arguments['pluginName']);;
		$this->setExpectedException('RuntimeException', NULL, 1346514748);
		$instance->initializeArgumentsAndRender();
	}
	/**
	 * @test
	 */
	public function supportsUseOfControllerAndActionSeparator() {
		$arguments = [
			'label' => 'Test field',
			'controllerExtensionName' => 'Flux',
			'pluginName' => 'API',
			'controllerName' => 'Flux',
			'actions' => [],
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => [],
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => [],
			'separator' => ' :: '
		];
		$instance = $this->buildViewHelperInstance($arguments, [], NULL, $arguments['extensionName'], $arguments['pluginName']);;
		$instance->initializeArgumentsAndRender();
		$component = $instance->getComponent();
		$this->assertSame($arguments['separator'], $component->getSeparator());
	}

	/**
	 * @test
	 */
	public function canGetCombinedExtensionKeyFromRequest() {
		$arguments = [
			'label' => 'Test field',
			'pluginName' => 'API',
			'controllerName' => 'Flux',
			'actions' => [],
			'disableLocalLanguageLabels' => FALSE,
			'excludeActions' => [],
			'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
			'prefixOnRequiredArguments' => '*',
			'subActions' => [],
			'separator' => ' :: '
		];
		$instance = $this->buildViewHelperInstance($arguments);
		$request = new Request();
		$request->setControllerExtensionName('Flux');
		$request->setControllerVendorName('FluidTYPO3');
		$expected = 'FluidTYPO3.Flux';
		$result = $this->callInaccessibleMethod($instance, 'getFullExtensionNameFromRequest', $request);
		$this->assertEquals($expected, $result);
	}

}
