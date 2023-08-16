<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;

class TableConfigurationPostProcessorTest extends AbstractTestCase
{
    protected $contentTypeBuilder;

    public function testProcessData(): void
    {
        if (!class_exists(TableConfigurationPostProcessingHookInterface::class)) {
            $this->markTestSkipped('Skipping test with TableConfigurationPostProcessingHookInterface dependency');
        }
        $applicator = $this->getMockBuilder(SpooledConfigurationApplicator::class)
            ->onlyMethods(['processData'])
            ->disableOriginalConstructor()
            ->getMock();
        $applicator->expects(self::once())->method('processData');
        $subject = new TableConfigurationPostProcessor($applicator);
        $subject->processData();
        ;
    }
}
