<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\PreviewOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\PreviewRenderer;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class PreviewRendererTest extends AbstractTestCase
{
    private PageRenderer $pageRenderer;
    private ProviderResolver $providerResolver;

    protected function setUp(): void
    {
        $this->pageRenderer = $this->getMockBuilder(PageRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testRenderPreviewSkipsProvidersWithoutForms(): void
    {
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getPreview')->willReturn(['header', 'content', false]);
        $provider->method('getForm')->willReturn(null);

        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([$provider]);

        $record = ['uid' => 123];

        $subject = $this->getMockBuilder(PreviewRenderer::class)
            ->onlyMethods(['attachAssets'])
            ->setConstructorArgs([$this->pageRenderer, $this->providerResolver])
            ->getMock();

        [$headerContent, $itemContent, $drawItem] = $subject->renderPreview($record, 'h', 'c');

        self::assertSame(true, $drawItem);
        self::assertSame('h', $headerContent);
        self::assertSame('c', $itemContent);
    }

    /**
     * @dataProvider getRenderPreviewTestValues
     */
    public function testRenderPreview(
        ?string $expectedHeader,
        ?string $expectedBody,
        bool $continue,
        ?string $mode,
        ?string $currentHeader,
        ?string $currentPreview
    ): void {
        $form = Form::create();
        if ($mode) {
            $form->setOption(PreviewOption::PREVIEW, [PreviewOption::MODE => $mode]);
        }

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getPreview')->willReturn(['header', 'content', false]);
        $provider->method('getForm')->willReturn($form);

        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([$provider]);

        $record = ['uid' => 123];

        $subject = $this->getMockBuilder(PreviewRenderer::class)
            ->onlyMethods(['attachAssets'])
            ->setConstructorArgs([$this->pageRenderer, $this->providerResolver])
            ->getMock();

        [$headerContent, $itemContent, $drawItem] = $subject->renderPreview($record, $currentHeader, $currentPreview);

        self::assertSame($continue, $drawItem);
        self::assertSame($expectedHeader, $headerContent);
        self::assertSame($expectedBody, $itemContent);
    }

    public function getRenderPreviewTestValues(): array
    {
        $link = '<a name="c123"></a>';
        return [
            'no current, mode append' => ['header', $link . 'content', false, PreviewOption::MODE_APPEND, null, null],
            'no current, mode prepend' => ['header', $link . 'content', false, PreviewOption::MODE_PREPEND, null, null],
            'no current, mode replace' => ['header', $link . 'content', false, PreviewOption::MODE_REPLACE, null, null],
            'no current, mode none' => [null, null, true, PreviewOption::MODE_NONE, null, null],
            'no current, mode not set' => ['header', $link . 'content', false, null, null, null],
            'current, mode append' => ['h: header', $link . 'ccontent', false, PreviewOption::MODE_APPEND, 'h', 'c'],
            'current, mode prepend' => ['header: h', $link . 'contentc', false, PreviewOption::MODE_PREPEND, 'h', 'c'],
            'current, mode replace' => ['header', $link . 'content', false, PreviewOption::MODE_REPLACE, 'h', 'c'],
            'current, mode none' => ['h', 'c', true, PreviewOption::MODE_NONE, 'h', 'c'],
            'current, mode not set' => ['header', $link . 'content', false, null, 'h', 'c'],
        ];
    }

    public function testAttachAssets(): void
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.4', '>=')) {
            $this->markTestSkipped('Skipping PreviewRenderer asset attach on 12.4 - feature inoperable');
        }
        $this->pageRenderer->expects($this->atLeastOnce())->method('loadRequireJsModule');
        $subject = new PreviewRenderer($this->pageRenderer, $this->providerResolver);
        $this->callInaccessibleMethod($subject, 'attachAssets');
    }
}
