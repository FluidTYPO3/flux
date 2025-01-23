<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeValidator;
use FluidTYPO3\Flux\Integration\FormEngine\ContentTypeValidatorNode;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ContentTypeValidatorNodeTest extends AbstractTestCase
{
    public function testRender(): void
    {
        $nodeFactory = $this->getMockBuilder(NodeFactory::class)->disableOriginalConstructor()->getMock();
        $data = [
            'parameterArray' => ['foo' => 'bar'],
            'databaseRow' => ['uid' => 123],
        ];
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            $subject = $this->getMockBuilder(ContentTypeValidatorNode::class)
                ->setMethods(['initializeResultArray'])
                ->getMock();
            $subject->setData($data);
        } else {
            $subject = $this->getMockBuilder(ContentTypeValidatorNode::class)
                ->setMethods(['initializeResultArray'])
                ->setConstructorArgs([$nodeFactory, $data])
                ->getMock();
        }
        $subject->method('initializeResultArray')->willReturn([]);

        $userFunction = $this->getMockBuilder(ContentTypeValidator::class)
            ->setMethods(['validateContentTypeRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $userFunction->expects(self::once())
            ->method('validateContentTypeRecord')
            ->with($data['parameterArray'] + ['row' => $data['databaseRow']])
            ->willReturn('html');
        GeneralUtility::addInstance(ContentTypeValidator::class, $userFunction);

        $output = $subject->render();
        self::assertSame(['html' => 'html'], $output);
    }
}
