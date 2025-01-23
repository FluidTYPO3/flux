<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\ColumnPositionNode;
use FluidTYPO3\Flux\Integration\FormEngine\UserFunctions;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ColumnPositionNodeTest extends AbstractTestCase
{
    public function testRender(): void
    {
        $data = [
            'parameterArray' => [],
        ];
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            $subject = $this->getMockBuilder(ColumnPositionNode::class)
                ->setMethods(['initializeResultArray'])
                ->getMock();
            $subject->setData($data);
        } else {
            $nodeFactory = $this->getMockBuilder(NodeFactory::class)->disableOriginalConstructor()->getMock();
            $subject = $this->getMockBuilder(ColumnPositionNode::class)
                ->setMethods(['initializeResultArray'])
                ->setConstructorArgs([$nodeFactory, $data])
                ->getMock();
        }

        $subject->method('initializeResultArray')->willReturn([]);

        $userFunction = $this->getMockBuilder(UserFunctions::class)
            ->setMethods(['renderColumnPositionField'])
            ->disableOriginalConstructor()
            ->getMock();
        $userFunction->method('renderColumnPositionField')->willReturn('html');
        GeneralUtility::addInstance(UserFunctions::class, $userFunction);

        $output = $subject->render();
        self::assertSame(['html' => 'html'], $output);
    }
}
