<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Overrides\BackendLayoutView;
use FluidTYPO3\Flux\Integration\Overrides\PageLayoutView;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class PageLayoutViewTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '>')) {
            $this->markTestSkipped('Skipping test with PageLayoutView dependency');
        }

        parent::setUp();
    }

    public function testSetAndGetPageInfo(): void
    {
        $instance = $this->getMockBuilder(PageLayoutView::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $info = ['foo' => 'bar'];
        $instance->setPageinfo($info);
        $this->assertSame($info, $instance->getPageinfo());
        $result = $instance->getPageinfo();
        $this->assertSame($info, $result);
    }

    public function testGetBackendLayoutView(): void
    {
        $singletons = GeneralUtility::getSingletonInstances();

        $instance = $this->getMockBuilder(PageLayoutView::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance->setProvider($this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass());
        $instance->setRecord(['uid' => 123]);

        $backendLayoutView = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::setSingletonInstance(BackendLayoutView::class, $backendLayoutView);

        $output = $this->callInaccessibleMethod($instance, 'getBackendLayoutView');

        GeneralUtility::resetSingletonInstances($singletons);

        self::assertSame($backendLayoutView, $output);
    }
}
