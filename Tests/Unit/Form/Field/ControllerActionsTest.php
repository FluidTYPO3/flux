<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Field\ControllerActions;

/**
 * ControllerActionsTest
 */
class ControllerActionsTest extends AbstractFieldTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'switchableControllerActions',
        'label' => 'Test field',
        'enable' => true,
        'controllerExtensionName' => 'FluidTYPO3.Flux',
        'pluginName' => 'API',
        'controllerName' => 'Flux',
        'actions' => array(),
        'disableLocalLanguageLabels' => false,
        'excludeActions' => array(),
        'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang.xlf',
        'prefixOnRequiredArguments' => '*',
        'subActions' => array()
    );

    /**
     * @test
     */
    public function canUseRawItems()
    {
        $component = $this->createInstance();
        $items = array(
            array('foo' => 'Foo'),
            array('bar' => 'Bar')
        );
        $component->setItems($items);
        $this->assertSame($items, $component->getItems());
    }

    /**
     * @test
     */
    public function canSetAndGetSeparator()
    {
        $component = $this->createInstance();
        $separator = ' :: ';
        $component->setSeparator($separator);
        $this->assertSame($separator, $component->getSeparator());
    }

    /**
     * @test
     */
    public function convertActionListToArrayReturnsSameValueIfAlreadyArray()
    {
        $component = $this->createInstance();
        $input = array();
        $output = $this->callInaccessibleMethod($component, 'convertActionListToArray', $input);
        $this->assertEquals($input, $output);
    }

    /**
     * @test
     */
    public function returnsNullIfBuiltControllerClassNameDoesNotExist()
    {
        $component = $this->createInstance();
        $component->setControllerExtensionName('doesnotexist');
        $className = $this->callInaccessibleMethod($component, 'buildExpectedAndExistingControllerClassName', 'Content');
        $this->assertNull($className);
    }

    /**
     * @test
     */
    public function acceptsNamespacedClasses()
    {
        $expectedClassName = 'FluidTYPO3\Flux\Controller\ContentController';
        $component = $this->createInstance();
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $className = $this->callInaccessibleMethod($component, 'buildExpectedAndExistingControllerClassName', 'Content');
        $this->assertSame($expectedClassName, $className);
    }

    /**
     * @test
     */
    public function acceptsLegacyNamedClasses()
    {
        $expectedClassName = 'Tx_Flux_Controller_ContentController';
        $component = $this->createInstance();
        $component->setControllerExtensionName('flux');
        $className = $this->callInaccessibleMethod($component, 'buildExpectedAndExistingControllerClassName', 'Content');
        $this->assertSame($expectedClassName, $className);
    }

    /**
     * @test
     */
    public function canGenerateLabelFromLanguageFile()
    {
        $extensionKey = 'flux';
        $pluginName = 'Test';
        $controllerName = 'Content';
        $actionName = 'fake';
        $localLanguageFileRelativePath = '/Resources/Private/Language/locallang.xlf';
        $labelPath = strtolower($pluginName . '.' . $controllerName . '.' . $actionName);
        $expectedLabel = 'LLL:EXT:' . $extensionKey . $localLanguageFileRelativePath . ':' . $labelPath;
        $label = $this->buildLabelForControllerAndAction($controllerName, $actionName, $localLanguageFileRelativePath);
        $this->assertSame($expectedLabel, $label);
    }

    /**
     * @test
     */
    public function canGenerateLabelFromActionMethodAnnotation()
    {
        $controllerName = 'Content';
        $actionName = 'fake';
        $expectedLabel = 'Fake Action';
        $label = $this->buildLabelForControllerAndAction($controllerName, $actionName);
        $this->assertSame($expectedLabel, $label);
    }

    /**
     * @test
     */
    public function canGenerateDefaultLabelFromActionMethodWithoutHumanReadableAnnotation()
    {
        $controllerName = 'Content';
        $actionName = 'fakeWithoutDescription';
        $expectedLabel = $actionName . '->' . $controllerName;
        $label = $this->buildLabelForControllerAndAction($controllerName, $actionName);
        $this->assertSame($expectedLabel, $label);
    }

    /**
     * @test
     */
    public function generatesDefaultLabelForControllerActionsWhichDoNotExist()
    {
        $controllerName = 'Content';
        $actionName = 'fictionalaction';
        $expectedLabel = $actionName . '->' . $controllerName;
        $label = $this->buildLabelForControllerAndAction($controllerName, $actionName);
        $this->assertSame($expectedLabel, $label);
    }

    /**
     * @test
     */
    public function prefixesLabelForActionsWithRequiredArgumentsWhenLanguageLabelsDisabled()
    {
        $extensionName = 'FluidTYPO3.Flux';
        $pluginName = 'Test';
        $controllerName = 'Content';
        $actionName = 'fakeWithRequiredArgument';
        $component = $this->createInstance();
        $component->setControllerExtensionName($extensionName);
        $component->setPluginName($pluginName);
        $component->setControllerName($controllerName);
        $component->setDisableLocalLanguageLabels(true);
        $label = $this->callInaccessibleMethod($component, 'getLabelForControllerAction', $controllerName, $actionName);
        $prefixedLabel = $this->callInaccessibleMethod($component, 'prefixLabel', $controllerName, $actionName, $label);
        $this->assertStringStartsWith('*', $prefixedLabel);
        $this->assertNotSame($label, $prefixedLabel);
    }

    /**
     * @test
     */
    public function respectsExcludedActions()
    {
        $actions = array(
            'Content' => 'render,fake'
        );
        $excludedActions = array(
            'Content' => 'fake',
        );
        /** @var ControllerActions $component */
        $component = $this->createInstance();
        $component->setExcludeActions($excludedActions);
        $component->setActions($actions);
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $items = $this->buildActions($component, false);
        foreach ($items as $item) {
            $this->assertArrayNotHasKey('Content->fake', $item);
        }
    }

    /**
     * @test
     */
    public function skipsOtherControllersInActionsIfControllerSpecifiedInBothPropertyAndActions()
    {
        $actions = array(
            'Content' => 'fake',
            'Other' => 'fake'
        );
        class_alias('FluidTYPO3\Flux\Controller\ContentController', 'FluidTYPO3\Flux\Controller\OtherController');
        /** @var ControllerActions $component */
        $component = $this->createInstance();
        $component->setActions($actions);
        $component->setControllerName('Content');
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $items = $this->buildActions($component, false);
        foreach ($items as $item) {
            $this->assertArrayNotHasKey('Other->fake', $item);
        }
    }

    /**
     * @test
     */
    public function skipsActionsWhichDoNotHaveAssociatedControllerMethods()
    {
        $actions = array(
            'Content' => 'fake,doesNotExist'
        );
        /** @var ControllerActions $component */
        $component = $this->createInstance();
        $component->setActions($actions);
        $component->setControllerName('Content');
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $items = $this->buildActions($component, false);
        foreach ($items as $item) {
            $this->assertArrayNotHasKey('Other->doesNotExist', $item);
        }
    }

    /**
     * @test
     */
    public function supportsSubActions()
    {
        $actions = array(
            'Content' => 'fake'
        );
        $subActions = array(
            'Content' => array(
                'fake' => 'render'
            )
        );
        $expected = array(
            array('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:.content.fake', 'Content->fake;Content->render')
        );
        /** @var ControllerActions $component */
        $component = $this->createInstance();
        $component->setActions($actions);
        $component->setSubActions($subActions);
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $component->setControllerName('Content');
        $items = $this->buildActions($component, false);
        $this->assertSame($expected, $items);
    }

    /**
     * @test
     */
    public function getActionsForExtensionNameAndPluginNameReturnsExpectedValue()
    {
        $instance = $this->createInstance();
        $instance->setPluginName('None');
        $instance->setControllerExtensionName('FluidTYPO3.Flux');
        $output = $this->callInaccessibleMethod($instance, 'getActionsForExtensionNameAndPluginName');
        $expected = array();
        $this->assertEquals($expected, $output);
    }

    /**
     * @test
     */
    public function buildItemsForActionsSkipsNonExistingControllerNames()
    {
        $instance = $this->createInstance();
        $instance->setControllerExtensionName('FluidTYPO3.Flux');
        $actions = array(
            'Content' => 'render',
            'DoesNotExist' => 'render'
        );
        $expected = array(
            array(
                'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:.content.render',
                'Content->render'
            )
        );
        $output = $this->callInaccessibleMethod($instance, 'buildItemsForActions', $actions);
        $this->assertEquals($expected, $output);
    }

    /**
     * @param ControllerActions $component
     * @param boolean $useDefaults
     * @return array
     */
    protected function buildActions(ControllerActions $component, $useDefaults = true)
    {
        $actions = $component->getActions();
        if (true === $useDefaults) {
            $component->setControllerExtensionName('FluidTYPO3.Flux');
            $component->setPluginName('Test');
            $component->setControllerName('Content');
            $component->setLocalLanguageFileRelativePath('/Resources/Private/Language/locallang.xlf');
        }
        $items = $this->callInaccessibleMethod($component, 'buildItemsForActions', $actions);
        return $items;

    }

    /**
     * @param string $controllerName
     * @param string $actionName
     * @param string $languageFileRelativeLocation
     * @return string
     */
    protected function buildLabelForControllerAndAction($controllerName, $actionName, $languageFileRelativeLocation = null)
    {
        $component = $this->createInstance();
        $component->setControllerName($controllerName);
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $component->setPluginName('Test');
        if (null !== $languageFileRelativeLocation) {
            $component->setLocalLanguageFileRelativePath($languageFileRelativeLocation);
        } else {
            $component->setDisableLocalLanguageLabels(true);
        }
        $label = $this->callInaccessibleMethod($component, 'getLabelForControllerAction', $controllerName, $actionName);
        return $label;
    }

    /**
     * @disabledtest
     */
    public function prefixesParentObjectNameToAutoLabelIfInsideObject()
    {

    }

    /**
     * @test
     */
    public function canGenerateLocalisableLabel()
    {
        $instance = $this->createInstance();
        $instance->setLabel(null);
        $instance->setExtensionName('Flux');
        /** @var Form $form */
        $instance->setName('testFormId');
        $form = Form::create(array(
            'name' => 'test',
            'extensionName' => 'flux'
        ));
        $form->add($instance);
        $label = $instance->getLabel();
        $this->assertContains('switchableControllerActions', $label);
        $this->assertStringStartsWith('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux', $label);
    }

    /**
     * @test
     */
    public function getActionsForExtensionNameAndPluginNameReturnsExpectedArray()
    {
        $instance = $this->createInstance();
        $instance->setControllerExtensionName('Extension');
        $instance->setPluginName('Plugin');
        $actions = array('Controller' => array('actions' => array('action1', 'action2')));
        $expected = array('Controller' => array('action1', 'action2'));
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Extension']['plugins']['Plugin']['controllers'] = $actions;
        $result = $this->callInaccessibleMethod($instance, 'getActionsForExtensionNameAndPluginName');
        $this->assertEquals($expected, $result);
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']);
    }
}
