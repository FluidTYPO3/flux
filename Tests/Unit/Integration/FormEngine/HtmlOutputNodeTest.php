<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\HtmlOutputNode;
use FluidTYPO3\Flux\Integration\FormEngine\UserFunctions;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class HtmlOutputNodeTest extends AbstractTestCase
{
    public function testRender(): void
    {

        $data = [
            'parameterArray' => ['foo' => 'bar'],
            'databaseRow' => ['uid' => 123],
        ];
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            $subject = $this->getMockBuilder(HtmlOutputNode::class)
                ->onlyMethods(['initializeResultArray'])
                ->getMock();
            $subject->setData($data);
        } else {
            $nodeFactory = $this->getMockBuilder(NodeFactory::class)->disableOriginalConstructor()->getMock();
            $subject = $this->getMockBuilder(HtmlOutputNode::class)
                ->onlyMethods(['initializeResultArray'])
                ->setConstructorArgs([$nodeFactory, $data])
                ->getMock();
        }

        $userFunction = $this->getMockBuilder(UserFunctions::class)
            ->onlyMethods(['renderHtmlOutputField'])
            ->disableOriginalConstructor()
            ->getMock();
        $userFunction->method('renderHtmlOutputField')->willReturn('html');
        GeneralUtility::addInstance(UserFunctions::class, $userFunction);

        $output = $subject->render();
        self::assertSame(['html' => 'html'], $output);
    }
}
