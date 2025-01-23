<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Event\PageContentPreviewRenderingEventListener;
use FluidTYPO3\Flux\Integration\PreviewRenderer;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class PageContentPreviewRenderingEventListenerTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(PageContentPreviewRenderingEvent::class)) {
            self::markTestSkipped('Event implementation not available on current TYPO3 version');
        }

        parent::setUp();
    }

    /**
     * @dataProvider getRenderPreviewTestValues
     */
    public function testRenderPreview(?string $expected, string $table, string $preview): void
    {
        $context = $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock();
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            $event = new PageContentPreviewRenderingEvent($table, 'type', [], $context);
        } else {
            $event = new PageContentPreviewRenderingEvent($table, [], $context);
        }

        $previewRenderer = $this->getMockBuilder(PreviewRenderer::class)
            ->onlyMethods(['renderPreview'])
            ->disableOriginalConstructor()
            ->getMock();
        $previewRenderer->method('renderPreview')->willReturn(['', $preview, true]);
        GeneralUtility::addInstance(PreviewRenderer::class, $previewRenderer);

        $subject = new PageContentPreviewRenderingEventListener();
        $subject->renderPreview($event);

        self::assertSame($expected, $event->getPreviewContent());
    }

    public function getRenderPreviewTestValues(): array
    {
        return [
            'with mismatched table' => [null, 'mismatched', ''],
            'without preview' => ['', 'tt_content', ''],
            'with preview' => ['preview', 'tt_content', 'preview'],
        ];
    }
}
