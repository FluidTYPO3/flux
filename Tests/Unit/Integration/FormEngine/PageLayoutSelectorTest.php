<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\FormEngine\PageLayoutSelector;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Package\MetaData;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class PageLayoutSelectorTest extends AbstractTestCase
{
    private array $singletons = [];

    protected function setUp(): void
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '<')) {
            $this->markTestSkipped('Skipping test on TYPO3v10 - feature not supported on that version');
        }
        parent::setUp();

        $this->singletons = GeneralUtility::getSingletonInstances();

        $pageService = $this->getMockBuilder(PageService::class)->disableOriginalConstructor()->getMock();
        $packageManager = $this->getMockBuilder(PackageManager::class)->disableOriginalConstructor()->getMock();
        $pageRenderer = $this->getMockBuilder(PageRenderer::class)->disableOriginalConstructor()->getMock();

        $packageMetaData = $this->getMockBuilder(MetaData::class)->disableOriginalConstructor()->getMock();
        if (method_exists($packageMetaData, 'getTitle')) {
            $packageMetaData->method('getTitle')->willReturn('package-title');
        } else {
            $packageMetaData->method('getPackageKey')->willReturn('foobar');
        }

        $package = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $package->method('getPackageMetaData')->willReturn($packageMetaData);
        $packageManager->method('getPackage')->willReturn($package);

        $form = Form::create();
        $form->setOption(FormOption::TEMPLATE_FILE_RELATIVE, 'Foobar');
        $form->setDescription('description');
        $form->setLabel('title');
        $pageService->method('getAvailablePageTemplateFiles')->willReturn(['Foo.Bar' => [$form]]);

        GeneralUtility::setSingletonInstance(PageService::class, $pageService);
        GeneralUtility::setSingletonInstance(PackageManager::class, $packageManager);
        GeneralUtility::setSingletonInstance(PageRenderer::class, $pageRenderer);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        GeneralUtility::resetSingletonInstances($this->singletons);
    }

    public function testRenderWithSelectedValue(): void
    {
        $html = $this->executeTest('Foo.Bar->foobar');
        self::assertStringContainsString(
            '<input type="radio" name="dataelementBaseName" value="Foo.Bar->foobar" checked="checked" />',
            $html
        );
        self::assertStringContainsString('Parent decides', $html);
        self::assertStringContainsString('<h4>Foobar</h4>', $html);
        self::assertStringContainsString('<p>description</p>', $html);
    }

    public function testRenderWithoutSelectedValue(): void
    {
        $html = $this->executeTest('');
        self::assertStringContainsString(
            '<input type="radio" name="dataelementBaseName" value="" checked="checked" />',
            $html
        );
        self::assertStringNotContainsString(
            '<input type="radio" name="dataelementBaseName" value="Foo.Bar->foobar" checked="checked" />',
            $html
        );
        self::assertStringContainsString('Parent decides', $html);
        self::assertStringContainsString('<h4>Foobar</h4>', $html);
        self::assertStringContainsString('<p>description</p>', $html);
    }

    private function executeTest(string $value): string
    {
        $nodeFactory = $this->getMockBuilder(NodeFactory::class)->disableOriginalConstructor()->getMock();
        $data = [
            'parameterArray' => ['foo' => 'bar'],
            'fieldName' => 'field',
            'elementBaseName' => 'elementBaseName',
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'iconHeight' => 300,
                        'titles' => true,
                        'descriptions' => true,
                    ],
                ],
            ],
            'databaseRow' => [
                'field' => $value,
            ],
        ];
        $subject = $this->getMockBuilder(PageLayoutSelector::class)
            ->onlyMethods(['initializeResultArray', 'resolveIconForForm', 'translate', 'attachAssets'])
            ->setConstructorArgs([$nodeFactory, $data])
            ->getMock();
        $subject->method('initializeResultArray')->willReturn([]);
        $subject->method('resolveIconForForm')->willReturn('icon');
        $subject->method('translate')->willReturnArgument('translated');

        return $subject->render()['html'];
    }
}
