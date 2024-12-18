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
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class PreviewRendererTest extends AbstractTestCase
{
    private PageRenderer $pageRenderer;
    private ProviderResolver $providerResolver;
    private string $registerMethodName;

    protected function setUp(): void
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            $this->registerMethodName = 'loadJavaScriptModule';
        } else {
            $this->registerMethodName = 'loadRequireJsModule';
        }

        $this->pageRenderer = $this->getMockBuilder(PageRenderer::class)
            ->onlyMethods([$this->registerMethodName])
            ->disableOriginalConstructor()
            ->getMock();
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testPreProcess(): void
    {
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getPreview')->willReturn(['header', 'content', false]);

        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([$provider]);

        $record = ['uid' => 123];

        $subject = $this->getMockBuilder(PreviewRenderer::class)
            ->onlyMethods(['attachAssets'])
            ->setConstructorArgs([$this->pageRenderer, $this->providerResolver])
            ->getMock();

        [$headerContent, $itemContent, $drawItem] = $subject->renderPreview($record);

        self::assertFalse($drawItem);
        self::assertSame('header', $headerContent);
        self::assertSame('<a name="c123"></a>content', $itemContent);
    }

    public function testAttachAssets(): void
    {
        $this->pageRenderer->expects($this->atLeastOnce())->method($this->registerMethodName);
        $subject = new PreviewRenderer($this->pageRenderer, $this->providerResolver);
        $this->callInaccessibleMethod($subject, 'attachAssets');
    }
}
