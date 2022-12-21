<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;

class PageServiceTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        $this->singletonInstances[FluxService::class] = $this->getMockBuilder(FluxService::class)
            ->setMethods(['getPageConfiguration', 'message', 'getFormFromTemplateFile'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->singletonInstances[WorkspacesAwareRecordService::class] = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->singletonInstances[ConfigurationManager::class] = $this->getMockBuilder(ConfigurationManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testGetPageFlexFormSourceWithZeroUidReturnsNull(): void
    {
        $this->assertNull((new PageService())->getPageFlexFormSource(0));
    }

    public function testGetPageTemplateConfigurationWithZeroUidReturnsNull(): void
    {
        $this->assertNull((new PageService())->getPageTemplateConfiguration(0));
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
        $instance = $this->getMockBuilder(PageService::class)->setMethods(['getRootLineUtility', 'getRuntimeCache'])->disableOriginalConstructor()->getMock();
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
        /** @var WorkspacesAwareRecordService|MockObject $service */
        $this->singletonInstances[WorkspacesAwareRecordService::class]->method('getSingle')->willReturnMap(
            [
                ['pages', 'uid,pid,t3ver_oid,tx_fed_page_flexform', 1, $record1],
                ['pages', 'uid,pid,t3ver_oid,tx_fed_page_flexform', 2, $record2],
            ]
        );
        $instance = new PageService();
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

        $this->singletonInstances[FluxService::class]->method('getFormFromTemplateFile')->willReturn(
            $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock()
        );
        $this->singletonInstances[FluxService::class]->method('getPageConfiguration')->willReturn($typoScript);

        $instance = $this->getMockBuilder(PageService::class)
            ->setMethods(['getRuntimeCache', 'getLogger', 'createTemplatePaths'])
            ->getMock();
        $instance->method('getRuntimeCache')->willReturn($runtimeCache);
        $instance->method('getLogger')->willReturn(
            $this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass()
        );
        $instance->method('createTemplatePaths')->willReturn($templatePaths);

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
