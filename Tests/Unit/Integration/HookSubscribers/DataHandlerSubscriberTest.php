<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\HookSubscribers\ContentUsedDecision;
use FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class DataHandlerSubscriberTest extends AbstractTestCase
{
    /**
     * @param array $parameters
     * @param bool $expectsCascading
     * @dataProvider getBeforeCommandMapCascadeTestValues
     */
    public function testBeforeCommandMapCascadesExpectedOperations(array $parameters, bool $expectsCascading)
    {
        $dataHandler = new DataHandler();
        $dataHandler->cmdmap = $parameters;
        $instance = $this->getMockBuilder(DataHandlerSubscriber::class)->setMethods(['cascadeCommandToChildRecords'])->getMock();
        $instance->expects($expectsCascading ? $this->once() : $this->never())->method('cascadeCommandToChildRecords');
        $instance->processCmdmap_beforeStart($dataHandler);
    }

    public function getBeforeCommandMapCascadeTestValues(): array
    {
        return [
            'command "copy" does not cascade' => [
                ['tt_content' => [123 => ['copy' => 321]]],
                false
            ],
            'command "copyToLanguage" cascades' => [
                ['tt_content' => [123 => ['copyToLanguage' => 321]]],
                true
            ],
            'command "localize" cascades' => [
                ['tt_content' => [123 => ['localize' => 321]]],
                true
            ],
            'command "undelete" cascades' => [
                ['tt_content' => [123 => ['undelete' => true]]],
                true
            ],
            'command "delete" cascades' => [
                ['tt_content' => [123 => ['delete' => true]]],
                true
            ],
        ];
    }
}
