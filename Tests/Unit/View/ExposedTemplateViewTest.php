<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\ExposedTemplateView;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Parser\TemplateParser as LegacyTemplateParser;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * ExposedTemplateViewTest
 */
class ExposedTemplateViewTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function getParsedTemplateReturnsCompiledTemplateIfFound()
    {
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(['getTemplateIdentifier'])->getMock();
        $instance->expects($this->any())->method('getTemplateIdentifier');
        $templateParserMock = $this->getMockBuilder(LegacyTemplateParser::class)->setMethods(['getOrParseAndStoreTemplate'])->getMock();
        $templateParserMock->expects($this->any())->method('getOrParseAndStoreTemplate')->willReturn('foobar');
        $templatePathsMock = $this->getMockBuilder(\TYPO3\CMS\Fluid\View\TemplatePaths::class)->setMethods(['getTemplateIdentifier'])->getMock();
        $compiler = $this->getMockBuilder('TYPO3\\CMS\\Fluid\\Core\\Compiler\\TemplateCompiler')->setMethods(['has', 'get'])->getMock();
        $compiler->expects($this->any())->method('has')->willReturn(true);
        $compiler->expects($this->any())->method('get')->willReturn('foobar');
        $context = $this->getMockBuilder(RenderingContext::class)->setMethods(['getTemplateParser', 'getTemplatePaths'])->getMock();
        $context->expects($this->any())->method('getTemplateParser')->willReturn($templateParserMock);
        $context->expects($this->any())->method('getTemplatePaths')->willReturn($templatePathsMock);

        ObjectAccess::setProperty($instance, 'baseRenderingContext', $context, true);
        ObjectAccess::setProperty($instance, 'templateCompiler', $compiler, true);
        $result = $this->callInaccessibleMethod($instance, 'getParsedTemplate');
        $this->assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function previewSectionIsOptional()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $preview = $view->renderStandaloneSection('Preview', array(), true);
        $this->assertStringMatchesFormat('', $preview);
    }

    /**
     * @test
     */
    public function canRenderEmptyPreviewSection()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW_EMPTY);
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $preview = $view->renderStandaloneSection('Preview', array(), true);
        $preview = trim($preview);
        $this->assertEmpty($preview);
    }

    /**
     * @test
     */
    public function canRenderCustomSection()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_CUSTOM_SECTION);
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $sectionContent = $view->renderStandaloneSection('Custom', array(), true);
        $sectionContent = trim($sectionContent);
        $this->assertEquals('This is a custom section. Do not change this placeholder text.', $sectionContent);
    }

    /**
     * @test
     */
    public function canRenderRaw()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_CUSTOM_SECTION);
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $sectionContent = $view->render();
        $sectionContent = trim($sectionContent);
        $this->assertEmpty($sectionContent);
        $this->assertNotContains('<', $sectionContent);
        $this->assertNotContains('>', $sectionContent);
    }

    /**
     * @test
     */
    public function canRenderWithDisabledCompiler()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_CUSTOM_SECTION);
        $backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'] = 1;
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $sectionContent = $view->render();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'] = $backup;
        $sectionContent = trim($sectionContent);
        $this->assertEmpty($sectionContent);
        $this->assertNotContains('<', $sectionContent);
        $this->assertNotContains('>', $sectionContent);
    }

    /**
     * @test
     */
    public function createsDefaultFormFromInvalidTemplate()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_WITHOUTFORM);
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $form = $view->getForm('Configuration');
        $this->assertIsValidAndWorkingFormObject($form);
    }

    /**
     * @test
     */
    public function renderingTemplateTwiceTriggersTemplateCompilerSaving()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $view->render();
        $form1 = $view->getForm();
        $form2 = $view->getForm();
        $this->assertSame($form1, $form2);
    }

    /**
     * @test
     */
    public function throwsRuntimeExceptionIfImproperlyInitialized()
    {
        $view = $this->objectManager->get('FluidTYPO3\Flux\View\ExposedTemplateView');
        $this->setExpectedException('RuntimeException', '', 1343521593);
        $this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
    }

    /**
     * @disabledtest
     */
    public function throwsParserExceptionIfTemplateSourceContainsErrors()
    {
        // @TODO: use vfs
        $validTemplatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
        $validTemplateSource = file_get_contents($validTemplatePathAndFilename);
        $invalidTemplateSource = $validTemplateSource . LF . LF . '</f:section>' . LF;
        $temporaryFilePathAndFilename = GeneralUtility::getFileAbsFileName('typo3temp/flux-temp-' . uniqid() . '.html');
        GeneralUtility::writeFile($temporaryFilePathAndFilename, $invalidTemplateSource);
        $view = $this->getPreparedViewWithTemplateFile($temporaryFilePathAndFilename);
        $this->setExpectedException('Tx_Fluid_Core_Parser_Exception');
        $this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
    }

    /**
     * @test
     */
    public function canGetStoredVariableWithoutConfigurationSectionName()
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
        $view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
        $result = $this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function canGetTemplateByActionName()
    {
        $templatePaths = $this->getFixtureTemplatePaths();
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext(null, 'Flux', 'Content');
        $viewContext->setTemplatePaths(new TemplatePaths($templatePaths));
        $view = $service->getPreparedExposedTemplateView($viewContext);
        $renderingContext = $view->getRenderingContext();
        if (method_exists($renderingContext, 'setControllerName')) {
            $paths = $this->getMockBuilder(\TYPO3\CMS\Fluid\View\TemplatePaths::class)
                ->setMethods(['resolveTemplateFileForControllerAndActionAndFormat'])
                ->getMock();
            $paths->expects($this->once())->method('resolveTemplateFileForControllerAndActionAndFormat')->willReturn(
                ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Templates/Content/Dummy.html')
            );
            $renderingContext->setTemplatePaths($paths);
        } else {
            $controllerContext = ObjectAccess::getProperty($view, 'controllerContext', true);
            $controllerContext->getRequest()->setControllerName('Content');
            $controllerContext->getRequest()->setControllerActionName('dummy');
            $view->setControllerContext($controllerContext);
            $renderingContext->setControllerContext($controllerContext);
        }
        $output = $view->getTemplatePathAndFilename('dummy');
        $this->assertNotEmpty($output);
        $this->assertFileExists($output);
    }

    /**
     * @param $templatePathAndFilename
     * @return ExposedTemplateView
     */
    protected function getPreparedViewWithTemplateFile($templatePathAndFilename)
    {
        $templatePaths = $this->getFixtureTemplatePaths();
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext($templatePathAndFilename, 'Flux', 'API');
        $viewContext->setTemplatePaths(new TemplatePaths($templatePaths));
        $view = $service->getPreparedExposedTemplateView($viewContext);
        return $view;
    }

    /**
     * @return array
     */
    protected function getFixtureTemplatePaths()
    {
        $templatePaths = array(
            'templateRootPaths' => array(ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Templates')),
            'partialRootPaths' => array(ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Partials')),
            'layoutRootPaths' => array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts'))
        );
        return $templatePaths;
    }
}
