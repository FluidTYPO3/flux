<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Development\ProtectedAccess;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

/**
 * ControllerActionsViewHelperTest
 */
class ControllerActionsViewHelperTest extends AbstractFieldViewHelperTestCase
{

    /**
     * @var array
     */
    protected $defaultArguments = array(
        'label' => 'Test field',
        'controllerExtensionName' => '',
        'pluginName' => 'Flux',
        'controllerName' => 'Content',
        'actions' => array(),
        'disableLocalLanguageLabels' => false,
        'excludeActions' => array(),
        'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
        'prefixOnRequiredArguments' => '*',
        'subActions' => array()
    );

    /**
     * @test
     */
    public function acceptsTraversableListOfActions()
    {
        $array = array('foo', 'bar');
        $traversable = new \ArrayIterator($array);
        $arguments = array(
            'label' => 'Test field',
            'controllerExtensionName' => 'Flux',
            'pluginName' => 'API',
            'controllerName' => 'Flux',
            'actions' => $traversable,
            'disableLocalLanguageLabels' => false,
            'excludeActions' => array(),
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => array()
        );
        $instance = $this->buildViewHelperInstance($arguments);
        $component = $instance->getComponent(
            ProtectedAccess::getProperty($instance, 'renderingContext'),
            ProtectedAccess::getProperty($instance, 'arguments')
        );
        $this->assertSame($array, $component->getActions());
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidExtensionPluginNameAndActionsCombination()
    {
        $this->markTestSkipped();
        $arguments = array(
            'label' => 'Test field',
            'controllerExtensionName' => '',
            'pluginName' => '',
            'controllerName' => '',
            'actions' => array(),
            'disableLocalLanguageLabels' => false,
            'excludeActions' => array(),
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => array()
        );
        $instance = $this->buildViewHelperInstance($arguments, array(), null, $arguments['extensionName'] ?? null, $arguments['pluginName'] ?? null);
        ;
        $this->expectExceptionCode(1346514748);
        $instance->initializeArgumentsAndRender();
    }
    /**
     * @test
     */
    public function supportsUseOfControllerAndActionSeparator()
    {
        $arguments = array(
            'label' => 'Test field',
            'controllerExtensionName' => 'Flux',
            'pluginName' => 'API',
            'controllerName' => 'Flux',
            'actions' => array(),
            'disableLocalLanguageLabels' => false,
            'excludeActions' => array(),
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => array(),
            'separator' => ' :: '
        );
        $instance = $this->buildViewHelperInstance($arguments, array(), null, $arguments['extensionName'] ?? null, $arguments['pluginName'] ?? null);
        ;
        $instance->initializeArgumentsAndRender();
        $component = $instance->getComponent(
            ProtectedAccess::getProperty($instance, 'renderingContext'),
            ProtectedAccess::getProperty($instance, 'arguments')
        );
        $this->assertSame($arguments['separator'], $component->getSeparator());
    }

    /**
     * @test
     */
    public function canGetCombinedExtensionKeyFromRequest()
    {
        $arguments = array(
            'label' => 'Test field',
            'pluginName' => 'API',
            'controllerName' => 'Flux',
            'actions' => array(),
            'disableLocalLanguageLabels' => false,
            'excludeActions' => array(),
            'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang_db.xml',
            'prefixOnRequiredArguments' => '*',
            'subActions' => array(),
            'separator' => ' :: '
        );
        $instance = $this->buildViewHelperInstance($arguments);
        $request = new Request();
        $request->setControllerExtensionName('FluidTYPO3.Flux');
        if (method_exists($request, 'setControllerVendorName')) {
            $request->setControllerVendorName('FluidTYPO3');
        }
        $expected = 'FluidTYPO3.Flux';
        $result = $this->callInaccessibleMethod($instance, 'getFullExtensionNameFromRequest', $request);
        $this->assertEquals($expected, $result);
    }
}
