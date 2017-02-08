<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * Class RecordListGetTableHookSubscriberTest
 */
class RecordListGetTableHookSubscriberTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function testModifiesClauseWhenTableIsMatched()
    {
        $subject = new RecordListGetTableHookSubscriber();
        $selectedFields = array();
        $reference = null;
        $clause = null;
        $subject->getDBlistQuery('tt_content', 1, $clause, $selectedFields, $reference);
        $this->assertEquals(' AND colPos <> ' . ContentService::COLPOS_FLUXCONTENT, $clause);
    }

    /**
     * @test
     */
    public function testDoesNotModifyClauseIfTableIsNotMatched()
    {
        $subject = new RecordListGetTableHookSubscriber();
        $selectedFields = array();
        $reference = null;
        $clause = null;
        $subject->getDBlistQuery('pages', 1, $clause, $selectedFields, $reference);
        $this->assertNull($clause);
    }
}
