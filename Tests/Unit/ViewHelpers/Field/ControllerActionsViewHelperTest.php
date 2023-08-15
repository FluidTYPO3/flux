<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\ContentController;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;

class ControllerActionsViewHelperTest extends AbstractFieldViewHelperTestCase
{
    protected array $defaultArguments = [
        'label' => 'Test field',
        'controllerExtensionName' => '',
        'pluginName' => 'Flux',
        'controllerName' => 'Content',
        'actions' => [],
        'disableLocalLanguageLabels' => false,
        'excludeActions' => [],
        'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
        'prefixOnRequiredArguments' => '*',
        'subActions' => []
    ];

    /**
     * @test
     */
    public function acceptsTraversableListOfActions(): void
    {
        $array = ['foo', 'bar'];
        $traversable = new \ArrayIterator($array);
        $arguments = [
            'label' => 'Test field',
            'controllerExtensionName' => 'Flux',
            'pluginName' => 'API',
            'controllerName' => 'Flux',
            'actions' => $traversable,
            'disableLocalLanguageLabels' => false,
            'excludeActions' => [],
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => []
        ];
        $instance = $this->buildViewHelperInstance($arguments);
        $component = $instance->getComponent(
            $this->renderingContext,
            $this->buildViewHelperArguments($instance, $arguments)
        );
        $this->assertSame($array, $component->getActions());
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidExtensionPluginNameAndActionsCombination(): void
    {
        $arguments = [
            'label' => 'Test field',
            'controllerExtensionName' => '',
            'extensionName' => '',
            'pluginName' => '',
            'controllerName' => '',
            'actions' => [],
            'disableLocalLanguageLabels' => false,
            'excludeActions' => [],
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => []
        ];
        $instance = $this->buildViewHelperInstance(
            $arguments,
            [],
            null,
            $arguments['extensionName'],
            $arguments['pluginName']
        );
        $this->expectExceptionCode(1346514748);
        $instance->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function supportsUseOfControllerAndActionSeparator(): void
    {
        $arguments = [
            'label' => 'Test field',
            'controllerExtensionName' => 'Flux',
            'pluginName' => 'API',
            'extensionName' => '',
            'controllerName' => 'Flux',
            'actions' => [],
            'disableLocalLanguageLabels' => false,
            'excludeActions' => [],
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => [],
            'separator' => ' :: '
        ];
        $instance = $this->buildViewHelperInstance(
            $arguments,
            [],
            null,
            $arguments['extensionName'],
            $arguments['pluginName']
        );
        $instance->initializeArgumentsAndRender();
        $component = $instance->getComponent(
            $this->renderingContext,
            $this->buildViewHelperArguments($instance, $arguments)
        );
        $this->assertSame($arguments['separator'], $component->getSeparator());
    }

    /**
     * @test
     */
    public function canGetCombinedExtensionKeyFromRequest(): void
    {
        $arguments = [
            'label' => 'Test field',
            'pluginName' => 'API',
            'controllerName' => 'Flux',
            'actions' => [],
            'disableLocalLanguageLabels' => false,
            'excludeActions' => [],
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => [],
            'separator' => ' :: '
        ];
        $instance = $this->buildViewHelperInstance($arguments);
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['getExtbaseAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        if (class_exists(ExtbaseRequestParameters::class)) {
            $parameters = new ExtbaseRequestParameters(ContentController::class);
            $parameters->setControllerExtensionName('Flux');
            $request->method('getExtbaseAttribute')->willReturn($parameters);
        } else {
            $request->setControllerExtensionName('Flux');
        }
        if (method_exists($request, 'setControllerVendorName')) {
            $request->setControllerVendorName('FluidTYPO3');
            $expected = $expected = 'FluidTYPO3.Flux';
        } else {
            $expected = 'Flux';
        }
        $result = $this->callInaccessibleMethod($instance, 'getFullExtensionNameFromRequest', $request);
        $this->assertEquals($expected, $result);
    }
}
