<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\Preview;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * PreviewTest
 */
class PreviewTest extends AbstractTestCase
{

    /**
     * Setup
     */
    public function setUp()
    {
        $configurationManager = $this->getMockBuilder(ConfigurationManager::class)->getMock();
        $fluxService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
        $fluxService->injectConfigurationManager($configurationManager);
        $tempFiles = (array) glob(GeneralUtility::getFileAbsFileName('typo3temp/flux-preview-*.tmp'));
        foreach ($tempFiles as $tempFile) {
            if (true === file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * @test
     */
    public function canExecuteRenderer()
    {
        $caller = $this->getMockBuilder('TYPO3\CMS\Backend\View\PageLayoutView')->setMethods(array('attachAssets'))->disableOriginalConstructor()->getMock();
        $function = 'FluidTYPO3\Flux\Backend\Preview';
        $result = $this->callUserFunction($function, $caller);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function canGetPageTitleAndPidFromContentUid()
    {
        $instance = new Preview();
        $recordService = $this->getMockBuilder(RecordService::class)->setMethods(['get'])->getMock();
        $recordService->expects($this->once())->method('get')->willReturn([['foo']]);
        ObjectAccess::setProperty($instance, 'recordService', $recordService, true);
        $result = $this->callInaccessibleMethod($instance, 'getPageTitleAndPidFromContentUid', 1);
        $this->assertSame(['foo'], $result);
    }

    /**
     * @param string $function
     * @param mixed $caller
     */
    protected function callUserFunction($function, $caller)
    {
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $row['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
        Core::registerConfigurationProvider('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider');
        $instance = $this->getMockBuilder($function)->setMethods(array('attachAssets'))->getMock();
        $instance->preProcess($caller, $drawItem, $headerContent, $itemContent, $row);
        Core::unregisterConfigurationProvider('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider');
    }

    /**
     * @test
     */
    public function testAttachAssets()
    {
        $pageRenderer = $this->getMockBuilder(PageRenderer::class)->setMethods(['addRequireJsConfiguration', 'loadRequireJsModule'])->getMock();
        $pageRenderer->expects($this->atLeastOnce())->method('addRequireJsConfiguration');
        $pageRenderer->expects($this->atLeastOnce())->method('loadRequireJsModule');
        $document = $this->getMockBuilder(ModuleTemplate::class)->setMethods(['getPageRenderer'])->getMock();
        $document->expects($this->once())->method('getPageRenderer')->willReturn($pageRenderer);
        GeneralUtility::addInstance(ModuleTemplate::class, $document);
        $subject = $this->createInstance();
        $this->callInaccessibleMethod($subject, 'attachAssets');
    }
}
