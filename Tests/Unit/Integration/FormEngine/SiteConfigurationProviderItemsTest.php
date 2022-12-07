<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\FormEngine\SiteConfigurationProviderItems;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteConfigurationProviderItemsTest extends AbstractTestCase
{
    private ?PageService $pageService;

    protected function setUp(): void
    {
        $this->pageService = $this->getMockBuilder(PageService::class)
            ->setMethods(['getAvailablePageTemplateFiles'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->singletonInstances[PageService::class] = $this->pageService;

        parent::setUp();
    }

    public function testProcessContentTypeItems(): void
    {
        $singletons = GeneralUtility::getSingletonInstances();

        $contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['fetchContentTypeNames'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeManager->method('fetchContentTypeNames')->willReturn(['flux_test', 'flux_test2']);

        GeneralUtility::setSingletonInstance(ContentTypeManager::class, $contentTypeManager);

        $tca = ['items' => []];

        $expected = $tca;
        $expected['items'][] = ['flux_test', 'flux_test'];
        $expected['items'][] = ['flux_test2', 'flux_test2'];

        $subject = new SiteConfigurationProviderItems();
        $output = $subject->processContentTypeItems(
            $tca,
            $this->getMockBuilder(TcaSelectItems::class)->disableOriginalConstructor()->getMock()
        );

        self::assertSame(
            $expected,
            $output
        );

        GeneralUtility::resetSingletonInstances($singletons);
    }

    public function testProcessPageTemplateItems(): void
    {
        $form1 = Form::create();
        $form2 = Form::create();
        $form2->setOption(Form::OPTION_TEMPLATEFILE, 'test.html');

        $this->pageService->method('getAvailablePageTemplateFiles')->willReturn(
            [
                'test' => [
                    $form1,
                    $form2
                ]
            ]
        );

        $subject = $this->getMockBuilder(SiteConfigurationProviderItems::class)
            ->setMethods(['translate'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('translate')->willReturn('test');

        $tca = ['items' => []];

        $expected = $tca;
        $expected['items'][] = ['test', 'test->test'];

        self::assertSame(
            $expected,
            $subject->processPageTemplateItems(
                $tca,
                $this->getMockBuilder(TcaSelectItems::class)->disableOriginalConstructor()->getMock()
            )
        );
    }
}
