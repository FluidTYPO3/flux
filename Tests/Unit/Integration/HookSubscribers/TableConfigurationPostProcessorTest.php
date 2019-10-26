<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Lang\LanguageService;

/**
 * TableConfigurationPostProcessorTest
 */
class TableConfigurationPostProcessorTest extends AbstractTestCase
{
    protected function setUp()
    {
        $GLOBALS['LANG'] = $this->getMockBuilder(LanguageService::class)->setMethods(['sL'])->getMock();
    }

    /**
     * @test
     */
    public function canLoadProcessorAsUserObject()
    {
        $manager = $this->getMockBuilder(ContentTypeManager::class)->setMethods(['fetchContentTypes'])->getMock();
        $manager->expects($this->atLeastOnce())->method('fetchContentTypes')->willReturn([]);
        $object = $this->getMockBuilder(TableConfigurationPostProcessor::class)->setMethods(['getContentTypeManager'])->getMock();
        $object->expects($this->atLeastOnce())->method('getContentTypeManager')->willReturn($manager);
        $object->processData();
    }
}
