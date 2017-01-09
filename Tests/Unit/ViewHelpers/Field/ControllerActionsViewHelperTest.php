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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

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
            ObjectAccess::getProperty($instance, 'renderingContext', true),
            ObjectAccess::getProperty($instance, 'arguments', true)
        );
        $this->assertSame($array, $component->getActions());
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidExtensionPluginNameAndActionsCombination()
    {
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
        $instance = $this->buildViewHelperInstance($arguments, array(), null, $arguments['extensionName'], $arguments['pluginName']);
        ;
        $this->setExpectedException('RuntimeException', '', 1346514748);
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
        $instance = $this->buildViewHelperInstance($arguments, array(), null, $arguments['extensionName'], $arguments['pluginName']);
        ;
        $instance->initializeArgumentsAndRender();
        $component = $instance->getComponent(
            ObjectAccess::getProperty($instance, 'renderingContext', true),
            ObjectAccess::getProperty($instance, 'arguments', true)
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
        $request->setControllerExtensionName('Flux');
        $request->setControllerVendorName('FluidTYPO3');
        $expected = 'FluidTYPO3.Flux';
        $result = $this->callInaccessibleMethod($instance, 'getFullExtensionNameFromRequest', $request);
        $this->assertEquals($expected, $result);
    }
}
