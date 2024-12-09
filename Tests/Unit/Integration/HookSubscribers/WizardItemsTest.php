<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\HookSubscribers\WizardItems;
use FluidTYPO3\Flux\Integration\WizardItemsManipulator;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;

class WizardItemsTest extends AbstractTestCase
{
    private WizardItemsManipulator $wizardItemsManipulator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wizardItemsManipulator = $this->getMockBuilder(WizardItemsManipulator::class)
            ->onlyMethods(['manipulateWizardItems'])
            ->disableOriginalConstructor()
            ->getMock();

        $GLOBALS['TYPO3_REQUEST'] = $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass();
    }

    public function testDelegatesToWizardItemsManipulator(): void
    {
        $GLOBALS['TYPO3_REQUEST']->method('getQueryParams')->willReturn(['id' => 123, 'colPos' => 12]);

        $pageUid = 123;
        $columnPosition = 12;
        $controller = $this->getMockBuilder(NewContentElementController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $items = [];
        $this->wizardItemsManipulator->expects(self::once())->method('manipulateWizardItems')->with(
            $items,
            $pageUid,
            $columnPosition
        );
        $subject = new WizardItems($this->wizardItemsManipulator);
        $subject->manipulateWizardItems($items, $controller);
    }

    public function testReadsPageUidAndColumnPositionFromParentObjectIfNotPresentInRequestArguments(): void
    {
        $GLOBALS['TYPO3_REQUEST']->method('getQueryParams')->willReturn([]);

        $pageUid = 123;
        $columnPosition = 12;
        $controller = $this->getMockBuilder(NewContentElementController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setInaccessiblePropertyValue($controller, 'id', $pageUid);
        $this->setInaccessiblePropertyValue($controller, 'colPos', $columnPosition);

        $items = [];
        $this->wizardItemsManipulator->expects(self::once())->method('manipulateWizardItems')->with(
            $items,
            $pageUid,
            $columnPosition
        );
        $subject = new WizardItems($this->wizardItemsManipulator);
        $subject->manipulateWizardItems($items, $controller);
    }
}
