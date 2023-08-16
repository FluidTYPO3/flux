<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Integration\HookSubscribers\Preview;
use FluidTYPO3\Flux\Integration\PreviewRenderer;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PreviewTest extends AbstractTestCase
{
    public function setUp(): void
    {
        if (!class_exists(PageLayoutViewDrawItemHookInterface::class)) {
            $this->markTestSkipped('Skipping test with PageLayoutViewDrawItemHookInterface dependency');
        }
        $formDataTransformer = $this->getMockBuilder(FormDataTransformer::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->singletonInstances[FormDataTransformer::class] = $formDataTransformer;

        $tempFiles = (array) glob('typo3temp/flux-preview-*.tmp');
        foreach ($tempFiles as $tempFile) {
            if (true === file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        parent::setUp();
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
        $instance = $this->getMockBuilder($function)->setMethods(['attachAssets'])->getMock();
        $instance->preProcess($caller, $drawItem, $headerContent, $itemContent, $row);
        Core::unregisterConfigurationProvider(DummyConfigurationProvider::class);
    }

    public function testDelegatesToPreviewRenderer(): void
    {
        $renderer = $this->getMockBuilder(PreviewRenderer::class)
            ->setMethods(['renderPreview'])
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())->method('renderPreview')->willReturn(['a', 'b', true]);
        GeneralUtility::addInstance(PreviewRenderer::class, $renderer);

        $subject = new Preview();

        $parentObject = $this->getMockBuilder(PageLayoutView::class)->disableOriginalConstructor()->getMock();
        $drawItem = false;
        $headerContent = 'header';
        $itemContent = 'content';
        $record = ['uid' => 1];

        $subject->preProcess($parentObject, $drawItem, $headerContent, $itemContent, $record);
    }
}
