<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewRenderer;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PreviewRendererTest extends AbstractTestCase
{
    public function testPreProcess(): void
    {
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getPreview')->willReturn(['header', 'content', false]);

        $configurationService = $this->getMockBuilder(FluxService::class)
            ->setMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();
        $configurationService->method('resolveConfigurationProviders')->willReturn([$provider]);

        $record = ['uid' => 123];

        $subject = $this->getMockBuilder(PreviewRenderer::class)
            ->setMethods(['getConfigurationService', 'attachAssets'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getConfigurationService')->willReturn($configurationService);

        [$headerContent, $itemContent, $drawItem] = $subject->renderPreview($record);

        self::assertFalse($drawItem);
        self::assertSame('header', $headerContent);
        self::assertSame('<a name="c123"></a>content', $itemContent);
    }

    public function testAttachAssets(): void
    {
        $pageRenderer = $this->getMockBuilder(PageRenderer::class)->setMethods(['loadRequireJsModule'])->disableOriginalConstructor()->getMock();
        $pageRenderer->expects($this->atLeastOnce())->method('loadRequireJsModule');
        $instances = GeneralUtility::getSingletonInstances();
        GeneralUtility::setSingletonInstance(PageRenderer::class, $pageRenderer);
        $subject = $this->createInstance();
        $this->callInaccessibleMethod($subject, 'attachAssets');
        GeneralUtility::resetSingletonInstances($instances);
    }
}
