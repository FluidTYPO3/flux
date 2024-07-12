<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleCore;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyPageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     */
    public function testGetPageTemplateConfiguration(array $records, ?array $expected): void
    {
        $runtimeCache = new VariableFrontend(
            'runtime',
            $this->getMockBuilder(BackendInterface::class)->onlyMethods(['get'])->getMockForAbstractClass()
        );
        $runtimeCache->getBackend()->method('get')->willReturn('N;');
        $instance = $this->getMockBuilder(DummyPageService::class)
            ->onlyMethods(['getRootLine'])
            ->getMock();
        $instance->setRuntimeCache($runtimeCache);
        $instance->method('getRootLine')->willReturn($records);
        $result = $instance->getPageTemplateConfiguration(1);
        $this->assertEquals($expected, $result);
    }

    public function getPageTemplateConfigurationTestValues(): array
    {
        $m = 'tx_fed_page_controller_action';
        $s = 'tx_fed_page_controller_action_sub';
        $bothDefined = [$m => 'test1->test1', $s => 'test2->test2'];
        $subDefined = [$s => 'test2->test2'];
        return [
            'no pages in tree returns null' => [[[]], null],
            'empty selections in tree returns null' => [[[$m => '', $s => '']], null],
            'both actions defined returns both' => [
                [$bothDefined],
                $bothDefined + ['record_main' => null, 'record_sub' => $bothDefined]
            ],
            'sub action defined returns both' => [
                [[$m => ''], $subDefined],
                [$m => 'test2->test2', $s => 'test2->test2', 'record_main' => $subDefined, 'record_sub' => $subDefined]
            ],
        ];
    }

    public function testGetPageFlexFormSource(): void
    {
        $record1 = ['pid' => 2, 'uid' => 1];
        $record2 = ['pid' => 0, 'uid' => 3, 'tx_fed_page_flexform' => 'test'];
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->onlyMethods(['getSingle'])
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
        $renderingContext = new RenderingContext();

        $templateView = $this->getMockBuilder(TemplateView::class)
            ->onlyMethods(['getRenderingContext'])
            ->setConstructorArgs([$renderingContext])
            ->getMock();
        $templateView->method('getRenderingContext')->willReturn($renderingContext);

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->onlyMethods(['getTemplateRootPaths', 'ensureAbsolutePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $templatePaths->method('getTemplateRootPaths')->willReturn([__DIR__ . '/../../Fixtures/Templates']);
        $templatePaths->method('ensureAbsolutePath')->willReturnArgument(0);

        $instance = $this->getMockBuilder(DummyPageService::class)
            ->onlyMethods(['createTemplatePaths', 'getPageConfiguration'])
            ->getMock();
        $instance->setLogger($this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass());
        $instance->method('createTemplatePaths')->willReturn($templatePaths);
        $instance->method('getPageConfiguration')->willReturn($typoScript);

        // There are exactly 2 page template fixtures. We need to return exactly two mock instances of TemplateView.
        GeneralUtility::addInstance(TemplateView::class, $templateView);
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
        return [
            [[], null],
            [['test' => ['enable' => false]], null],
            [
                ['flux' => ['templateRootPaths' => ['Dummy']]],
                ['flux' => ['Dummy']]
            ],
            [
                ['flux' => ['templateRootPaths' => ['Invalid']]],
                ['flux' => null]
            ],
            [
                ['flux' => ['templateRootPaths' => ['Resources/Private/Templates/']]],
                ['flux' => null]
            ],
        ];
    }

    /**
     * @dataProvider getPageConfigurationInvalidTestValues
     * @param mixed $input
     */
    public function testGetPageConfigurationReturnsEmptyArrayOnInvalidInput($input): void
    {
        $instance = new DummyPageService();
        $result = $instance->getPageConfiguration($input);
        $this->assertEquals([], $result);
    }

    public function getPageConfigurationInvalidTestValues(): array
    {
        return [
            [''],
            [0],
        ];
    }

    public function testGetPageConfigurationReturnsEmptyArrayOnInvalidPlugAndPlayDirectorySetting(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][ExtensionOption::OPTION_PLUG_AND_PLAY] = true;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY]
            = ['foo'];

        $instance = new DummyPageService();

        $result = $instance->getPageConfiguration('Flux');
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'], $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']);

        self::assertEquals([], $result);
    }

    public function testGetPageConfigurationReturnsExpectedArrayOnPlugAndPlayDirectorySetting(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][ExtensionOption::OPTION_PLUG_AND_PLAY] = true;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY]
            = './';

        $instance = new DummyPageService();

        $result = $instance->getPageConfiguration('Flux');
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'], $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']);

        self::assertEquals(
            [
                TemplatePaths::CONFIG_TEMPLATEROOTPATHS => ['/Templates/Page/'],
                TemplatePaths::CONFIG_PARTIALROOTPATHS => ['/Partials/'],
                TemplatePaths::CONFIG_LAYOUTROOTPATHS => ['/Layouts/'],
            ],
            $result
        );
    }

    public function testGetPageConfigurationReturnsExpectedArrayOnPlugAndPlayDirectorySettingWithForeignExt(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][ExtensionOption::OPTION_PLUG_AND_PLAY] = true;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY]
            = './';

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->onlyMethods(['toArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $templatePaths->method('toArray')->willReturn(['foo' => 'bar']);

        $instance = $this->getMockBuilder(PageService::class)
            ->onlyMethods(['createTemplatePaths'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);

        Core::registerProviderExtensionKey('FluidTYPO3.Testing', 'Page');
        $result = $instance->getPageConfiguration(null);
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'], $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']);
        AccessibleCore::resetQueuedRegistrations();

        self::assertEquals(
            [
                'FluidTYPO3.Testing' => ['foo' => 'bar'],
                'FluidTYPO3.Flux' => [
                    TemplatePaths::CONFIG_TEMPLATEROOTPATHS => ['/Templates/Page/'],
                    TemplatePaths::CONFIG_PARTIALROOTPATHS => ['/Partials/'],
                    TemplatePaths::CONFIG_LAYOUTROOTPATHS => ['/Layouts/'],
                ],
            ],
            $result
        );
    }

    public function testGetPageConfigurationReturnsDefaultTemplatePaths(): void
    {
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->onlyMethods(['toArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $templatePaths->method('toArray')->willReturn(['foo' => 'bar']);

        $instance = $this->getMockBuilder(PageService::class)
            ->onlyMethods(['createTemplatePaths'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);

        $result = $instance->getPageConfiguration('Flux');

        self::assertEquals(['foo' => 'bar'], $result);
    }

    public function testGetPageConfigurationWithoutExtensionNameReadsRegisteredProviders(): void
    {
        $templatePaths = new TemplatePaths();
        $instance = $this->getMockBuilder(PageService::class)
            ->onlyMethods(['createTemplatePaths'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);
        Core::registerProviderExtensionKey('foo', 'Page');
        Core::registerProviderExtensionKey('bar', 'Page');
        $result = $instance->getPageConfiguration();
        $this->assertCount(2, $result);
    }
}
