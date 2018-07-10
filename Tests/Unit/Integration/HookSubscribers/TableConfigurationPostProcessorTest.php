<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $object = GeneralUtility::makeInstance(TableConfigurationPostProcessor::class);
        $object->processData();
        $this->assertInstanceOf(TableConfigurationPostProcessor::class, $object);
    }

}
