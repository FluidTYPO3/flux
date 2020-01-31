<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\PageController;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class PageControllerTest
 */
class PageControllerTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function testPerformsInjections()
    {
        $instance = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(PageController::class);
        $this->assertAttributeInstanceOf(PageService::class, 'pageService', $instance);
        $this->assertAttributeInstanceOf(FluxService::class, 'pageConfigurationService', $instance);
    }

    /**
     * @return void
     */
    public function testGetRecordReadsFromTypoScriptFrontendController()
    {
        $GLOBALS['TSFE'] = (object) ['page' => ['foo' => 'bar']];
        /** @var PageController $subject */
        $subject = $this->getMockBuilder(PageController::class)->setMethods(array('dummy'))->getMock();
        $record = $subject->getRecord();
        $this->assertSame(['foo' => 'bar'], $record);
    }

    public function testInitializeProvider()
    {
        /** @var FluxService|\PHPUnit_Framework_MockObject_MockObject $pageConfigurationService */
        $pageConfigurationService = $this->getMockBuilder(
            FluxService::class
        )->setMethods(
            array(
                'resolvePrimaryConfigurationProvider',
            )
        )->getMock();
        /** @var PageService $pageService */
        $pageService = $this->getMockBuilder(
            PageService::class
        )->setMethods(
            array(
                'getPageTemplateConfiguration'
            )
        )->getMock();
        $pageConfigurationService->expects($this->once())->method('resolvePrimaryConfigurationProvider');
        /** @var PageController|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this->getMockBuilder(PageController::class)->setMethods(array('getRecord'))->getMock();
        $instance->expects($this->once())->method('getRecord')->willReturn(array());
        $instance->injectpageConfigurationService($pageConfigurationService);
        $instance->injectPageService($pageService);
        $this->callInaccessibleMethod($instance, 'initializeProvider');
    }
}
