<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\FlexFormBuilder;
use FluidTYPO3\Flux\Integration\HookSubscribers\DynamicFlexForm;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DynamicFlexFormTest
 */
class DynamicFlexFormTest extends AbstractTestCase
{
    protected ?FlexFormBuilder $flexFormBuilder = null;
    protected ?FlexFormTools $flexFormTools = null;

    protected function setUp(): void
    {
        $this->flexFormBuilder = $this->getMockBuilder(FlexFormBuilder::class)
            ->setMethods(['resolveDataStructureIdentifier', 'parseDataStructureByIdentifier'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->flexFormTools = $this->getMockBuilder(FlexFormTools::class)
            ->onlyMethods(['getDataStructureIdentifier'])
            ->disableOriginalConstructor()
            ->getMock();

        GeneralUtility::addInstance(FlexFormBuilder::class, $this->flexFormBuilder);
        GeneralUtility::addInstance(FlexFormTools::class, $this->flexFormTools);

        parent::setUp();
    }

    public function testCreatesInstancesInConstructor(): void
    {
        $subject = new DynamicFlexForm();
        self::assertInstanceOf(
            FlexFormBuilder::class,
            $this->getInaccessiblePropertyValue($subject, 'flexFormBuilder')
        );
    }

    public function testGetDataStructureIdentifierPreProcessDelegatesToFlexFormBuilder(): void
    {
        $GLOBALS['TCA']['table']['columns']['field']['config'] = [];
        $subject = $this->getMockBuilder(DynamicFlexForm::class)
            ->setMethods(['getDataStructureIdentifier'])
            ->getMock();
        $subject->method('getDataStructureIdentifier')->willReturn('{"foo": "bar"}');
        $this->flexFormBuilder->method('resolveDataStructureIdentifier')
            ->with()
            ->willReturn(['foo' => 'bar']);
        $this->flexFormTools->method('getDataStructureIdentifier')->willReturn('{"id": 123}');
        $output = $subject->getDataStructureIdentifierPreProcess([], 'table', 'field', ['uid' => 1]);
        self::assertSame(['foo' => 'bar'], $output);
    }

    public function testParseDataStructureByIdentifierPreProcessDelegatesToFlexFormBuilder(): void
    {
        $identifier = ['foo' => 'bar'];
        $subject = new DynamicFlexForm();
        $this->flexFormBuilder->expects(self::once())->method('parseDataStructureByIdentifier')->with($identifier);
        $subject->parseDataStructureByIdentifierPreProcess($identifier);
    }
}
