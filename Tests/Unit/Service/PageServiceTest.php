<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyFluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyPageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;

class PageServiceTest extends AbstractTestCase
{
    public function testGetPageFlexFormSourceWithZeroUidReturnsNull(): void
    {
        $this->assertNull((new DummyPageService())->getPageFlexFormSource(0));
    }

    public function testGetPageTemplateConfigurationWithZeroUidReturnsNull(): void
    {
        $this->assertNull((new DummyPageService())->getPageTemplateConfiguration(0));
    }

    /**
     * @dataProvider getPageTemplateConfigurationTestValues
     * @param array $records
     * @param array|NULL $expected
     */
    public function testGetPageTemplateConfiguration(array $records, $expected): void
    {
        $rootLineUtility = $this->getMockBuilder(RootlineUtility::class)->setMethods(['get'])->disableOriginalConstructor()->getMock();
        $rootLineUtility->expects(self::once())->method('get')->willReturn($records);
        $runtimeCache = new VariableFrontend('runtime', $this->getMockBuilder(BackendInterface::class)->getMockForAbstractClass());
        $instance = $this->getMockBuilder(DummyPageService::class)->setMethods(['getRootLineUtility', 'getRuntimeCache'])->disableOriginalConstructor()->getMock();
        $instance->method('getRootLineUtility')->willReturn($rootLineUtility);
        $instance->method('getRuntimeCache')->willReturn($runtimeCache);
        $result = $instance->getPageTemplateConfiguration(1);
        $this->assertEquals($expected, $result);
    }

    public function getPageTemplateConfigurationTestValues(): array
    {
        $m = 'tx_fed_page_controller_action';
        $s = 'tx_fed_page_controller_action_sub';
        return array(
            array(array(array()), null),
            array(array(array($m => '', $s => '')), null),
            array(array(array($m => 'test1->test1', $s => 'test2->test2')), array($m => 'test1->test1', $s => 'test2->test2')),
            array(array(array($m => ''), array($s => 'test2->test2')), array($m => 'test2->test2', $s => 'test2->test2'))
        );
    }

    public function testGetPageFlexFormSource(): void
    {
        $record1 = array('pid' => 2, 'uid' => 1);
        $record2 = array('pid' => 0, 'uid' => 3, 'tx_fed_page_flexform' => 'test');
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->onlyMethods(['getSingle'])
            ->disableOriginalConstructor()
            ->getMock();
        $recordService->method('getSingle')->willReturnMap(
            [
                ['pages', 'uid,pid,t3ver_oid,tx_fed_page_flexform', 1, $record1],
                ['pages', 'uid,pid,t3ver_oid,tx_fed_page_flexform', 2, $record2],
            ]
        );
        $instance = new DummyPageService();
        $instance->setWorkspacesAwareRecordService($recordService);
        $output = $instance->getPageFlexFormSource(1);
        $this->assertEquals('test', $output);
    }

    /**
     * @dataProvider getAvailablePageTemplateFilesTestValues
     * @param string|array $typoScript
     * @param mixed $expected
     */
    public function testGetAvailablePageTemplateFiles($typoScript, $expected): void
    {
        $runtimeCache = new VariableFrontend(
            'runtime',
            $this->getMockBuilder(BackendInterface::class)->getMockForAbstractClass()
        );

        $renderingContext = new RenderingContext();

        $templateView = $this->getMockBuilder(TemplateView::class)
            ->setMethods(['getRenderingContext'])
            ->setConstructorArgs([$renderingContext])
            ->getMock();
        $templateView->method('getRenderingContext')->willReturn($renderingContext);

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->setMethods(['getTemplateRootPaths', 'ensureAbsolutePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $templatePaths->method('getTemplateRootPaths')->willReturn([__DIR__ . '/../../Fixtures/Templates']);
        $templatePaths->method('ensureAbsolutePath')->willReturnArgument(0);

        $fluxService = $this->getMockBuilder(DummyFluxService::class)
            ->onlyMethods(['getPageConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $fluxService->method('getPageConfiguration')->willReturn($typoScript);

        $instance = $this->getMockBuilder(DummyPageService::class)
            ->setMethods(['getRuntimeCache', 'getLogger', 'createTemplatePaths'])
            ->getMock();
        $instance->method('getRuntimeCache')->willReturn($runtimeCache);
        $instance->method('getLogger')->willReturn(
            $this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass()
        );
        $instance->method('createTemplatePaths')->willReturn($templatePaths);
        $instance->setConfigurationService($fluxService);

        GeneralUtility::addInstance(TemplateView::class, $templateView);

        $result = $instance->getAvailablePageTemplateFiles();
        if (null === $expected) {
            $this->assertEmpty($result);
        } else {
            $this->assertNotEmpty($result);
        }
    }

    public function getAvailablePageTemplateFilesTestValues(): array
    {
        return array(
            array(array(), null),
            array(array('test' => array('enable' => false)), null),
            array(
                array('flux' => array('templateRootPaths' => array('Dummy'))),
                array('flux' => array('Dummy'))
            ),
            array(
                array('flux' => array('templateRootPaths' => array('Invalid'))),
                array('flux' => null)
            ),
            array(
                array('flux' => array('templateRootPaths' => array('Resources/Private/Templates/'))),
                array('flux' => null)
            ),
        );
    }
}
