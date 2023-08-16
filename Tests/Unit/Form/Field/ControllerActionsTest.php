<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\ContentController;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Field\ControllerActions;
use FluidTYPO3\Flux\Form\FormInterface;

class ControllerActionsTest extends AbstractFieldTest
{
    protected array $chainProperties = [
        'name' => 'switchableControllerActions',
        'label' => 'Test field',
        'enabled' => true,
        'controllerExtensionName' => 'FluidTYPO3.Flux',
        'pluginName' => 'API',
        'controllerName' => 'Flux',
        'actions' => [],
        'disableLocalLanguageLabels' => false,
        'excludeActions' => [],
        'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang.xlf',
        'prefixOnRequiredArguments' => '*',
        'subActions' => []
    ];

    protected function createInstance(): FormInterface
    {
        $className = $this->getObjectClassName();
        $instance = $this->getMockBuilder($className)->setMethods(['resolvePathToFileInExtension'])->getMock();
        $instance->method('resolvePathToFileInExtension')->willReturn('./');
        return $instance;
    }

    /**
     * @test
     */
    public function canUseRawItems(): void
    {
        $component = $this->createInstance();
        $items = [
            ['foo' => 'Foo'],
            ['bar' => 'Bar']
        ];
        $component->setItems($items);
        $this->assertSame($items, $component->getItems());
    }

    /**
     * @test
     */
    public function canSetAndGetSeparator(): void
    {
        $component = $this->createInstance();
        $separator = ' :: ';
        $component->setSeparator($separator);
        $this->assertSame($separator, $component->getSeparator());
    }

    /**
     * @test
     */
    public function convertActionListToArrayReturnsSameValueIfAlreadyArray(): void
    {
        $component = $this->createInstance();
        $input = [];
        $output = $this->callInaccessibleMethod($component, 'convertActionListToArray', $input);
        $this->assertEquals($input, $output);
    }

    /**
     * @test
     */
    public function returnsNullIfBuiltControllerClassNameDoesNotExist(): void
    {
        $component = $this->createInstance();
        $component->setControllerExtensionName('doesnotexist');
        $className = $this->callInaccessibleMethod(
            $component,
            'buildExpectedAndExistingControllerClassName',
            'Content'
        );
        $this->assertNull($className);
    }

    /**
     * @test
     */
    public function acceptsNamespacedClasses(): void
    {
        $expectedClassName = ContentController::class;
        $component = $this->createInstance();
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $className = $this->callInaccessibleMethod(
            $component,
            'buildExpectedAndExistingControllerClassName',
            'Content'
        );
        $this->assertSame($expectedClassName, $className);
    }

    /**
     * @test
     */
    public function canGenerateLabelFromLanguageFile(): void
    {
        $extensionKey = 'flux';
        $pluginName = 'Test';
        $controllerName = 'Content';
        $actionName = 'fake';
        $localLanguageFileRelativePath = 'Resources/Private/Language/locallang.xlf';
        $labelPath = strtolower($pluginName . '.' . $controllerName . '.' . $actionName);
        $expectedLabel = 'LLL:EXT:' . $extensionKey . '/' . $localLanguageFileRelativePath . ':' . $labelPath;

        $component = $this->getMockBuilder($this->createInstanceClassName())
            ->onlyMethods(['resolvePathToFileInExtension'])
            ->getMock();
        $component->method('resolvePathToFileInExtension')->willReturn($localLanguageFileRelativePath);
        $component->setControllerName($controllerName);
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $component->setPluginName('Test');

        $label = $this->callInaccessibleMethod($component, 'getLabelForControllerAction', $controllerName, $actionName);

        $this->assertSame($expectedLabel, $label);
    }

    /**
     * @test
     */
    public function canGenerateLabelFromActionMethodAnnotation(): void
    {
        $controllerName = 'Content';
        $actionName = 'render';
        $expectedLabel = 'Render content';

        /** @var ControllerActions $component */
        $component = $this->getMockBuilder($this->createInstanceClassName())
            ->onlyMethods(['resolvePathToFileInExtension'])
            ->getMock();
        $component->method('resolvePathToFileInExtension')->willReturn('does/not/exist');
        $component->setControllerName($controllerName);
        $component->setControllerExtensionName('FluidTYPO3.Flux');
        $component->setPluginName('Test');
        $component->setDisableLocalLanguageLabels(true);

        $label = $this->callInaccessibleMethod($component, 'getLabelForControllerAction', $controllerName, $actionName);
        $this->assertSame($expectedLabel, $label);
    }

    /**
     * @test
     */
    public function canGenerateDefaultLabelFromActionMethodWithoutHumanReadableAnnotation(): void
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
    public function generatesDefaultLabelForControllerActionsWhichDoNotExist(): void
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
    public function prefixesLabelForActionsWithRequiredArgumentsWhenLanguageLabelsDisabled(): void
    {
        $extensionName = 'FluidTYPO3.Flux';
        $pluginName = 'Test';
        $controllerName = 'Content';
        $actionName = 'callSubController';

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
    public function respectsExcludedActions(): void
    {
        $actions = [
            'Content' => 'render,fake'
        ];
        $excludedActions = [
            'Content' => 'fake',
        ];
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
    public function skipsOtherControllersInActionsIfControllerSpecifiedInBothPropertyAndActions(): void
    {
        $actions = [
            'Content' => 'fake',
            'Other' => 'fake'
        ];
        class_alias(ContentController::class, 'FluidTYPO3\Flux\Controller\OtherController');
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
    public function skipsActionsWhichDoNotHaveAssociatedControllerMethods(): void
    {
        $actions = [
            'Content' => 'fake,doesNotExist'
        ];
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
    public function supportsSubActions(): void
    {
        $actions = [
            'Content' => 'fake'
        ];
        $subActions = [
            'Content' => [
                'fake' => 'render'
            ]
        ];
        $expected = [
            ['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:.content.fake', 'Content->fake;Content->render']
        ];
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
    public function getActionsForExtensionNameAndPluginNameReturnsExpectedValue(): void
    {
        $instance = $this->createInstance();
        $instance->setPluginName('None');
        $instance->setControllerExtensionName('FluidTYPO3.Flux');
        $output = $this->callInaccessibleMethod($instance, 'getActionsForExtensionNameAndPluginName');
        $expected = [];
        $this->assertEquals($expected, $output);
    }

    /**
     * @test
     */
    public function buildItemsForActionsSkipsNonExistingControllerNames(): void
    {
        $instance = $this->createInstance();
        $instance->setControllerExtensionName('FluidTYPO3.Flux');
        $actions = [
            'Content' => 'render',
            'DoesNotExist' => 'render'
        ];
        $expected = [
            [
                'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:.content.render',
                'Content->render'
            ]
        ];
        $output = $this->callInaccessibleMethod($instance, 'buildItemsForActions', $actions);
        $this->assertEquals($expected, $output);
    }

    protected function buildActions(ControllerActions $component, bool $useDefaults = true): array
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

    protected function buildLabelForControllerAndAction(
        string $controllerName,
        string $actionName,
        ?string $languageFileRelativeLocation = null
    ): string {
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
    public function prefixesParentObjectNameToAutoLabelIfInsideObject(): void
    {
    }

    /**
     * @test
     */
    public function canGenerateLocalisableLabel(): void
    {
        $instance = $this->createInstance();
        $instance->setLabel(null);
        $instance->setExtensionName('Flux');
        /** @var Form $form */
        $instance->setName('testFormId');
        $form = Form::create([
            'name' => 'test',
            'extensionName' => 'flux'
        ]);
        $form->add($instance);
        $label = $instance->getLabel();
        $this->assertStringContainsString('switchableControllerActions', $label);
        $this->assertStringStartsWith('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux', $label);
    }

    /**
     * @test
     */
    public function getActionsForExtensionNameAndPluginNameReturnsExpectedArray(): void
    {
        $instance = $this->createInstance();
        $instance->setControllerExtensionName('Extension');
        $instance->setPluginName('Plugin');
        $actions = ['Controller' => ['actions' => ['action1', 'action2']]];
        $expected = ['Controller' => ['action1', 'action2']];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Extension']['plugins']['Plugin']['controllers']
            = $actions;
        $result = $this->callInaccessibleMethod($instance, 'getActionsForExtensionNameAndPluginName');
        $this->assertEquals($expected, $result);
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']);
    }
}
