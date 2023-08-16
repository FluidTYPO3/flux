<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

abstract class AbstractFormTest extends AbstractTestCase
{
    protected array $chainProperties = ['name' => 'test', 'label' => 'Test field', 'enabled' => true];

    protected function createInstance(): FormInterface
    {
        $className = $this->getObjectClassName();
        $instance = new $className();
        return $instance;
    }

    /**
     * @test
     */
    public function canGetAndSetExtensionName(): void
    {
        $form = $this->createInstance();
        $form->setExtensionName('Flux');
        $this->assertEquals('Flux', $form->getExtensionName());
    }

    /**
     * @test
     */
    public function canGetAndSetVariables(): void
    {
        $variables = ['test' => 'foobar'];
        $this->assertGetterAndSetterWorks('variables', $variables, $variables, true);
    }

    /**
     * @test
     */
    public function canGetAndSetSingleVariable(): void
    {
        $test = 'foobar';
        $instance = $this->createInstance();
        $instance->setVariable('test', $test);
        $this->assertEquals($test, $instance->getVariable('test'));
    }

    /**
     * @test
     */
    public function canGetLabel(): void
    {
        $className = $this->getObjectClassName();
        $instance = new $className();
        $instance->setName('test');
        $instance->setExtensionName('FluidTYPO3.Flux');
        $label = $instance->getLabel();
        $this->assertNotEmpty($label);
    }

    /**
     * @test
     */
    public function canGenerateRawLabelWhenLanguageLabelsDisabled(): void
    {
        $instance = $this->createInstance();
        $instance->setLabel(null);
        $instance->setDisableLocalLanguageLabels(true);
        $this->assertNull($instance->getLabel());
    }

    /**
     * @test
     */
    public function canGenerateLocalisableLabel(): void
    {
        $instance = $this->createInstance();
        $instance->setLabel(null);
        $instance->setExtensionName('Flux');
        if (true === $instance instanceof Form) {
            $instance->setName('testFormId');
            $instance->setExtensionName('Flux');
        } else {
            /** @var Form $form */
            $instance->setName('testFormId');
            $form = Form::create([
                'name' => 'test',
                'extensionName' => 'flux'
            ]);
            $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
            $form->add($instance);
        }
        $label = $instance->getLabel();
        $this->assertStringContainsString('testFormId', $label);
        $this->assertStringStartsWith('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux', $label);
    }

    protected function getObjectClassName(): string
    {
        $class = get_class($this);
        $class = substr($class, 0, -4);
        $class = str_replace('\\Tests\\Unit', '', $class);
        return $class;
    }

    /**
     * @test
     */
    public function canChainAllChainableSetters(array $chainPropertiesAndValues = null): FormInterface
    {
        if (null === $chainPropertiesAndValues) {
            $chainPropertiesAndValues = $this->chainProperties;
        }
        $instance = $this->createInstance();
        foreach ($chainPropertiesAndValues as $propertyName => $propertValue) {
            $setterMethodName = 'set' . ucfirst($propertyName);
            $chained = call_user_func_array([$instance, $setterMethodName], [$propertValue]);
            $this->assertSame(
                $instance,
                $chained,
                'The setter ' . $setterMethodName . ' on ' . $this->getObjectClassName() . ' does not support chaining.'
            );
            if ($chained === $instance) {
                $instance = $chained;
            }
        }
        return $instance;
    }

    /**
     * @test
     */
    public function returnsNameInsteadOfEmptyLabelWhenFormsExtensionKeyAndLabelAreBothEmpty(): void
    {
        $name = true === isset($this->chainProperties['name']) ? $this->chainProperties['name'] : 'test';
        $instance = $this->createInstance();
        $instance->setExtensionName(null);
        $instance->setName($name);
        $instance->setLabel(null);
        $this->assertEquals($name, $instance->getLabel());
    }

    /**
     * @test
     */
    public function canCallAllGetterCounterpartsForChainableSetters(): void
    {
        $instance = $this->createInstance();
        foreach ($this->chainProperties as $propertyName => $propertValue) {
            $setterMethodName = 'set' . ucfirst($propertyName);
            $instance->$setterMethodName($propertValue);
            $result = ObjectAccess::getProperty($instance, $propertyName);
            $this->assertEquals($propertValue, $result);
        }
    }

    protected function performTestBuild(FormInterface $instance): array
    {
        $configuration = $instance->build();
        $this->assertIsArray($configuration);
        return $configuration;
    }

    /**
     * @test
     */
    public function canBuildConfiguration(): void
    {
        $instance = $this->canChainAllChainableSetters();
        $this->performTestBuild($instance);
    }

    /**
     * @test
     */
    public function canCreateFromDefinition(): void
    {
        $properties = $this->chainProperties;
        $class = $this->getObjectClassName();
        $type = implode('/', array_slice(explode('_', substr($class, 13)), 1));
        $properties['type'] = $type;
        $instance = call_user_func_array([$class, 'create'], [$properties]);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
    }

    /**
     * @test
     */
    public function canUseShorthandLanguageLabel(): void
    {
        $className = $this->getObjectClassName();
        $instance = $this->getMockBuilder($className)->onlyMethods(['getName', 'getRoot'])->getMock();
        $instance->expects($this->any())->method('getRoot')->will($this->returnValue(null));
        $instance->expects($this->once())->method('getName')->will($this->returnValue('form'));
        $instance->setLabel('LLL:tt_content.tx_flux_container');
        $result = $instance->getLabel();
        $this->assertSame(
            'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_container',
            $result
        );
    }

    /**
     * @test
     */
    public function canModifyProperties(): void
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->addMethods(['dummy'])->getMock();
        $properties = ['enabled' => false];
        $mock->modify($properties);
        $result = $mock->getEnabled();
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function canModifyVariablesSelectively()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->addMethods(['dummy'])->getMock();
        $mock->setVariables(['foo' => 'baz', 'abc' => 'xyz']);
        $properties = ['options' => ['foo' => 'bar']];
        $mock->modify($properties);
        $this->assertEquals('bar', $mock->getVariable('foo'));
        $this->assertEquals('xyz', $mock->getVariable('abc'));
    }
}
