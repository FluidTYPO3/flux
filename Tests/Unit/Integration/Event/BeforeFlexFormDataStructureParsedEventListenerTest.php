<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\FlexFormBuilder;
use FluidTYPO3\Flux\Integration\Event\BeforeFlexFormDataStructureParsedEventListener;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Configuration\Event\BeforeFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BeforeFlexFormDataStructureParsedEventListenerTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(BeforeFlexFormDataStructureParsedEvent::class)) {
            self::markTestSkipped('Event implementation not available on current TYPO3 version');
        }

        parent::setUp();
    }

    public function testApplyFluxFlexFormDataStructure(): void
    {
        $event = new BeforeFlexFormDataStructureParsedEvent(['foo' => 'bar']);

        $flexFormBuilder = $this->getMockBuilder(FlexFormBuilder::class)
            ->setMethods(['parseDataStructureByIdentifier'])
            ->disableOriginalConstructor()
            ->getMock();
        $flexFormBuilder->expects(self::once())->method('parseDataStructureByIdentifier')->willReturn(['foo' => 'bar']);
        GeneralUtility::addInstance(FlexFormBuilder::class, $flexFormBuilder);

        $subject = new BeforeFlexFormDataStructureParsedEventListener();
        $subject->applyFluxFlexFormDataStructure($event);

        self::assertSame(['foo' => 'bar'], $event->getDataStructure());
    }
}
