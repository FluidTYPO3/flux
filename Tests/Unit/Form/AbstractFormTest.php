<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package Flux
 */
abstract class AbstractFormTest extends AbstractTestCase {

	/**
	 * @var array
	 */
	protected $chainProperties = ['name' => 'test', 'label' => 'Test field', 'enabled' => TRUE];

	/**
	 * @return FormInterface
	 */
	protected function createInstance() {
		$className = $this->getObjectClassName();
		$instance = $this->objectManager->get($className);
		return $instance;
	}

	/**
	 * @test
	 */
	public function canGetAndSetExtensionName() {
		$form = $this->createInstance();
		$form->setExtensionName('Flux');
		$this->assertEquals('Flux', $form->getExtensionName());
	}

	/**
	 * @test
	 */
	public function canGetAndSetVariables() {
		$variables = ['test' => 'foobar'];
		$this->assertGetterAndSetterWorks('variables', $variables, $variables, TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetSingleVariable() {
		$test = 'foobar';
		$instance = $this->createInstance();
		$instance->setVariable('test', $test);
		$this->assertEquals($test, $instance->getVariable('test'));
	}

	/**
	 * @test
	 */
	public function canGetLabel() {
		$className = $this->getObjectClassName();
		$instance = $this->objectManager->get($className);
		$instance->setName('test');
		if (TRUE === $instance instanceof FieldInterface || TRUE === $instance instanceof ContainerInterface) {
			$form = Form::create(['extensionKey' => 'flux']);
			$form->add($instance);
		}
		$label = $instance->getLabel();
		$this->assertNotEmpty($label);
	}

	/**
	 * @test
	 */
	public function canGenerateRawLabelWhenLanguageLabelsDisabled() {
		$instance = $this->createInstance();
		$instance->setLabel(NULL);
		$instance->setDisableLocalLanguageLabels(TRUE);
		$this->assertNull($instance->getLabel());
	}

	/**
	 * @test
	 */
	public function canGenerateLocalisableLabel() {
		$instance = $this->createInstance();
		$instance->setLabel(NULL);
		$instance->setExtensionName('Flux');
		if (TRUE === $instance instanceof Form) {
			$instance->setName('testFormId');
			$instance->setExtensionName('Flux');
		} else {
			/** @var Form $form */
			$instance->setName('testFormId');
			$form = Form::create([
				'name' => 'test',
				'extensionName' => 'flux'
			]);
			$form->add($instance);
		}
		$label = $instance->getLabel();
		$this->assertContains('testFormId', $label);
		$this->assertStringStartsWith('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux', $label);
	}

	/**
	 * @return string
	 */
	protected function getObjectClassName() {
		$class = get_class($this);
		$class = substr($class, 0, -4);
		$class = str_replace('\\Tests\\Unit', '', $class);
		return $class;
	}

	/**
	 * @test
	 * @param array $chainPropertiesAndValues
	 * @return FieldInterface
	 */
	public function canChainAllChainableSetters($chainPropertiesAndValues = NULL) {
		if (NULL === $chainPropertiesAndValues) {
			$chainPropertiesAndValues = $this->chainProperties;
		}
		$instance = $this->createInstance();
		foreach ($chainPropertiesAndValues as $propertyName => $propertValue) {
			$setterMethodName = ObjectAccess::buildSetterMethodName($propertyName);
			$chained = call_user_func_array([$instance, $setterMethodName], [$propertValue]);
			$this->assertSame($instance, $chained, 'The setter ' . $setterMethodName . ' on ' . $this->getObjectClassName() . ' does not support chaining.');
			if ($chained === $instance) {
				$instance = $chained;
			}
		}
		return $instance;
	}

	/**
	 * @test
	 */
	public function returnsNameInsteadOfEmptyLabelWhenFormsExtensionKeyAndLabelAreBothEmpty() {
		$name = TRUE === isset($this->chainProperties['name']) ? $this->chainProperties['name'] : 'test';
		$instance = $this->createInstance();
		$instance->setExtensionName(NULL);
		$instance->setName($name);
		$instance->setLabel(NULL);
		$this->assertEquals($name, $instance->getLabel());
	}

	/**
	 * @test
	 */
	public function canCallAllGetterCounterpartsForChainableSetters() {
		$instance = $this->createInstance();
		foreach ($this->chainProperties as $propertyName => $propertValue) {
			$setterMethodName = ObjectAccess::buildSetterMethodName($propertyName);
			$instance->$setterMethodName($propertValue);
			$result = ObjectAccess::getProperty($instance, $propertyName);
			$this->assertEquals($propertValue, $result);
		}
	}

	/**
	 * @param Form\FormInterface $instance
	 * @return array
	 */
	protected function performTestBuild(FormInterface $instance) {
		$configuration = $instance->build();
		$this->assertIsArray($configuration);
		return $configuration;
	}

	/**
	 * @test
	 */
	public function canBuildConfiguration() {
		$instance = $this->canChainAllChainableSetters();
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canCreateFromDefinition() {
		$properties = [$this->chainProperties];
		$class = $this->getObjectClassName();
		$type = implode('/', array_slice(explode('_', substr($class, 13)), 1));
		$properties['type'] = $type;
		$instance = call_user_func_array([$class, 'create'], [$properties]);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
	}

	/**
	 * @test
	 */
	public function canUseShorthandLanguageLabel() {
		$className = $this->getObjectClassName();
		$instance = $this->getMock($className, ['getExtensionKey', 'getName', 'getRoot']);
		$instance->expects($this->never())->method('getExtensionKey');
		$instance->expects($this->once())->method('getRoot')->will($this->returnValue(NULL));
		$instance->expects($this->once())->method('getName')->will($this->returnValue('form'));
		$instance->setLabel('LLL:tt_content.tx_flux_container');
		$result = $instance->getLabel();
		$this->assertSame(LocalizationUtility::translate('tt_content.tx_flux_container', 'flux'), $result);
	}

	/**
	 * @disabledtest
	 * @dataProvider getLabelTranslationTestValues
	 * @param string $input
	 * @param string $extensionKey
	 * @param string $expectedOutput
	 */
	public function canTranslateLabelReference($input, $extensionKey, $expectedOutput) {
		$mock = $this->getMock($this->createInstanceClassName());
		$result = $this->callInaccessibleMethod($mock, 'translateLabelReference', $input, $extensionKey);
		$this->assertEquals($expectedOutput, $result);
	}

	/**
	 * @return array
	 */
	public function getLabelTranslationTestValues() {
		return [
			['label', NULL, 'label'],
			['LLL:tt_content.tx_flux_container', 'flux', 'Content Container'],
			['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_container', NULL, 'Content Container'],
			['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_container', 'flux', 'Content Container'],
		];
	}

	/**
	 * @test
	 */
	public function canModifyProperties() {
		$mock = $this->getMock($this->createInstanceClassName(), ['dummy']);
		$properties = ['enabled' => FALSE];
		$mock->modify($properties);
		$result = $mock->getEnabled();
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function canModifyVariablesSelectively() {
		$mock = $this->getMock($this->createInstanceClassName(), ['dummy']);
		$mock->setVariables(['foo' => 'baz', 'abc' => 'xyz']);
		$properties = ['options' => ['foo' => 'bar']];
		$mock->modify($properties);
		$this->assertEquals('bar', $mock->getVariable('foo'));
		$this->assertEquals('xyz', $mock->getVariable('abc'));
	}

}
