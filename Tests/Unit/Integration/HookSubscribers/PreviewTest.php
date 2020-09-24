<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

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
        $fluxService = $this->objectManager->get(FluxService::class);
        $fluxService->injectConfigurationManager($configurationManager);
        $tempFiles = (array) glob(GeneralUtility::getFileAbsFileName('typo3temp/flux-preview-*.tmp'));
        foreach ($tempFiles as $tempFile) {
            if (true === file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
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
        Core::registerConfigurationProvider(DummyConfigurationProvider::class);
        $instance = $this->getMockBuilder($function)->setMethods(array('attachAssets'))->getMock();
        $instance->preProcess($caller, $drawItem, $headerContent, $itemContent, $row);
        Core::unregisterConfigurationProvider(DummyConfigurationProvider::class);
    }

    /**
     * @test
     */
    public function testAttachAssets()
    {
        $pageRenderer = $this->getMockBuilder(PageRenderer::class)->setMethods(['loadRequireJsModule'])->getMock();
        $pageRenderer->expects($this->atLeastOnce())->method('loadRequireJsModule');
        $instances = GeneralUtility::getSingletonInstances();
        GeneralUtility::setSingletonInstance(PageRenderer::class, $pageRenderer);
        $subject = $this->createInstance();
        $this->callInaccessibleMethod($subject, 'attachAssets');
        GeneralUtility::resetSingletonInstances($instances);
    }
}
