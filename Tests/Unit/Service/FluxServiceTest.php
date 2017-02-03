<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FluxServiceTest
 */
class FluxServiceTest extends AbstractTestCase
{

    /**
     * Setup
     */
    public function setup()
    {
        $providers = Core::getRegisteredFlexFormProviders();
        if (true === in_array('FluidTYPO3\Flux\Service\FluxService', $providers)) {
            Core::unregisterConfigurationProvider('FluidTYPO3\Flux\Service\FluxService');
        }
    }

    /**
     * @test
     * @dataProvider getSortObjectsTestValues
     * @param array $input
     * @param string $sortBy
     * @param string $direction
     * @param array $expectedOutput
     */
    public function testSortObjectsByProperty($input, $sortBy, $direction, $expectedOutput)
    {
        $service = new FluxService();
        $sorted = $service->sortObjectsByProperty($input, $sortBy, $direction);
        $this->assertSame($expectedOutput, $sorted);
    }

    /**
     * @return array
     */
    public function getSortObjectsTestValues()
    {
        return array(
            array(
                array(array('foo' => 'b'), array('foo' => 'a')),
                'foo', 'ASC',
                array(1 => array('foo' => 'a'), 0 => array('foo' => 'b'))
            ),
            array(
                array('a1' => array('foo' => 'b'), 'a2' => array('foo' => 'a')),
                'foo', 'ASC',
                array('a2' => array('foo' => 'a'), 'a1' => array('foo' => 'b')),
            ),
            array(
                array('a1' => array('foo' => 'b'), 'a2' => array('foo' => 'a')),
                'foo', 'DESC',
                array('a1' => array('foo' => 'b'), 'a2' => array('foo' => 'a')),
            ),
        );
    }

    /**
     * @test
     */
    public function dispatchesMessageOnInvalidPathsReturned()
    {
        $className = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($className)->setMethods(array('getDefaultViewConfigurationForExtensionKey', 'getTypoScriptByPath', 'getAllTypoScript'))->getMock();
        $instance->expects($this->once())->method('getAllTypoScript')->willReturn(['foo' => 'bar']);
        $instance->expects($this->once())->method('getTypoScriptByPath')->will($this->returnValue(null));
        $instance->expects($this->once())->method('getDefaultViewConfigurationForExtensionKey')->will($this->returnValue(null));
        $instance->getViewConfigurationForExtensionName('Flux');
    }

    /**
     * @test
     */
    public function canInstantiateFluxService()
    {
        $service = $this->createFluxServiceInstance();
        $this->assertInstanceOf('FluidTYPO3\Flux\Service\FluxService', $service);
    }

    /**
     * @test
     */
    public function canFlushCache()
    {
        $service = $this->createFluxServiceInstance();
        $result = $service->flushCache();
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canCreateExposedViewWithoutExtensionNameAndControllerName()
    {
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext();
        $view = $service->getPreparedExposedTemplateView($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
    }

    /**
     * @test
     */
    public function canCreateExposedViewWithExtensionNameWithoutControllerName()
    {
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext(null, 'Flux');
        $view = $service->getPreparedExposedTemplateView($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
    }

    /**
     * @test
     */
    public function canCreateExposedViewWithExtensionNameAndControllerName()
    {
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext(null, 'Flux', 'API');
        $view = $service->getPreparedExposedTemplateView($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
    }

    /**
     * @test
     */
    public function canCreateExposedViewWithoutExtensionNameWithControllerName()
    {
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext(null, null, 'API');
        $view = $service->getPreparedExposedTemplateView($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
    }

    /**
     * @test
     */
    public function canResolvePrimaryConfigurationProviderWithEmptyArray()
    {
        $service = $this->createFluxServiceInstance();
        $service->injectProviderResolver($this->objectManager->get('FluidTYPO3\\Flux\\Provider\\ProviderResolver'));
        $result = $service->resolvePrimaryConfigurationProvider('tt_content', null);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canGetFormWithPaths()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
        $service = $this->createFluxServiceInstance();
        $paths = array(
            'templateRootPath' => 'EXT:flux/Resources/Private/Templates',
            'partialRootPath' => 'EXT:flux/Resources/Private/Partials',
            'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts'
        );
        $viewContext = new ViewContext($templatePathAndFilename, 'Flux');
        $viewContext->setSectionName('Configuration');
        $viewContext->setTemplatePaths(new TemplatePaths($paths));
        $form1 = $service->getFormFromTemplateFile($viewContext);
        $form2 = $service->getFormFromTemplateFile($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $form1);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $form2);
    }

    /**
     * @test
     */
    public function getFormReturnsNullOnInvalidFile()
    {
        $templatePathAndFilename = '/void/nothing';
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext($templatePathAndFilename);
        $form = $service->getFormFromTemplateFile($viewContext);
        $this->assertNull($form);
    }

    /**
     * @test
     */
    public function canGetFormWithPathsAndTriggerCache()
    {
        $templatePathAndFilename = GeneralUtility::getFileAbsFileName(self::FIXTURE_TEMPLATE_BASICGRID);
        $service = $this->createFluxServiceInstance();
        $paths = array(
            'templateRootPath' => 'EXT:flux/Tests/Fixtures/Templates/',
            'partialRootPath' => 'EXT:flux/Tests/Fixtures/Partials/',
            'layoutRootPath' => 'EXT:flux/Tests/Fixtures/Layouts/'
        );
        $viewContext = new ViewContext($templatePathAndFilename, 'Flux');
        $viewContext->setTemplatePaths(new TemplatePaths($paths));
        $viewContext->setSectionName('Configuration');
        $form = $service->getFormFromTemplateFile($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
        $readAgain = $service->getFormFromTemplateFile($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $readAgain);
    }

    /**
     * @test
     */
    public function canReadGridFromTemplateWithoutConvertingToDataStructure()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
        $form = $this->performBasicTemplateReadTest($templatePathAndFilename);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
    }

    /**
     * @test
     */
    public function canRenderTemplateWithCompactingSwitchedOn()
    {
        $backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '1';
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_COMPACTED);
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext($templatePathAndFilename);
        $paths = array(
            'templateRootPath' => 'EXT:flux/Tests/Fixtures/Templates/',
            'partialRootPath' => 'EXT:flux/Tests/Fixtures/Partials/',
            'layoutRootPath' => 'EXT:flux/Tests/Fixtures/Layouts/'
        );
        $viewContext->setTemplatePaths(new TemplatePaths($paths));
        $viewContext->setSectionName('Configuration');
        $form = $service->getFormFromTemplateFile($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
        $stored = $form->build();
        $this->assertIsArray($stored);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = $backup;
    }

    /**
     * @test
     */
    public function canRenderTemplateWithCompactingSwitchedOff()
    {
        $backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '0';
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_SHEETS);
        $this->performBasicTemplateReadTest($templatePathAndFilename);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = $backup;
    }

    /**
     * @test
     */
    public function canGetBackendViewConfigurationForExtensionName()
    {
        $service = $this->createFluxServiceInstance();
        $config = $service->getBackendViewConfigurationForExtensionName('noname');
        $this->assertEmpty($config);
    }

    /**
     * @test
     */
    public function canGetViewConfigurationForExtensionNameWhichDoesNotExistAndConstructDefaults()
    {
        $expected = array(
            'templateRootPaths' => array(0 => 'EXT:void/Resources/Private/Templates/'),
            'partialRootPaths' => array(0 => 'EXT:void/Resources/Private/Partials/'),
            'layoutRootPaths' => array(0 => 'EXT:void/Resources/Private/Layouts/'),
        );
        $service = $this->createFluxServiceInstance();
        $config = $service->getViewConfigurationForExtensionName('void');
        $this->assertSame($expected, $config);
    }

    /**
     * @test
     */
    public function testGetSettingsForExtensionName()
    {
        $instance = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('getTypoScriptByPath'))->getMock();
        $instance->expects($this->once())->method('getTypoScriptByPath')
            ->with('plugin.tx_underscore.settings')
            ->willReturn(array('test' => 'test'));
        $result = $instance->getSettingsForExtensionName('under_score');
        $this->assertEquals(array('test' => 'test'), $result);
    }

    /**
     * @test
     */
    public function templateWithErrorReturnsFormWithErrorReporter()
    {
        $viewContext = new ViewContext($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW));
        $instance = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('getPreparedExposedTemplateView'))->getMock();
        $instance->expects($this->once())->method('getPreparedExposedTemplateView')->willThrowException(new \RuntimeException());
        $instance->injectObjectManager($this->objectManager);
        $form = $instance->getFormFromTemplateFile($viewContext);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\Field\UserFunction', reset($form->getFields()));
        $this->assertEquals('FluidTYPO3\Flux\UserFunction\ErrorReporter->renderField', reset($form->getFields())->getFunction());
    }

    /**
     * @test
     */
    public function messageIgnoresRepeatedMessages()
    {
        $instance = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('logMessage'))->getMock();
        $instance->expects($this->once())->method('logMessage');
        $instance->message('Test', 'Test', 2);
        $instance->message('Test', 'Test', 2);
    }

    /**
     * @test
     */
    public function testDebug()
    {
        $exception = new \RuntimeException('Test');
        $instance = $this->createInstance();
        $result = $instance->debug($exception);
        $this->assertNull($result);
    }

    /**
     * @test
     * @dataProvider getConvertFlexFormContentToArrayTestValues
     * @param string $flexFormContent
     * @param Form|NULL $form
     * @param string|NULL $languagePointer
     * @param string|NULL $valuePointer
     * @param array $expected
     */
    public function testConvertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer, $expected)
    {
        $instance = $this->createInstance();
        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getConvertFlexFormContentToArrayTestValues()
    {
        return array(
            array('', null, '', '', array()),
            array('', Form::create(), '', '', array()),
            array(Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD, Form::create(), '', '', array('settings' => array('input' => 0)))
        );
    }

    /**
     * @test
     */
    public function testGetAllTypoScriptCache()
    {
        $fluxService = $this->createFluxServiceInstance(array('getCurrentPageId'));

        $configurationManager = $this->getMockBuilder('FluidTYPO3\Flux\Configuration\ConfigurationManager')->setMethods(array('getConfiguration'))->getMock();
        $fluxService->injectConfigurationManager($configurationManager);
        $configurationManager->expects($this->once(0))->method('getConfiguration')->willReturn(['foo' => 'bar']);

        $this->assertNotNull($fluxService->getAllTypoScript());
        $this->assertNotNull($fluxService->getAllTypoScript());
    }
}
