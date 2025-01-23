<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\ProtectValueWizard;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ProtectValueWizardTest extends AbstractTestCase
{
    public function test(): void
    {
        $data = [
            'elementBaseName' => '[foo][bar][baz]',
        ];
        $nodeFactory = $this->getMockBuilder(NodeFactory::class)->disableOriginalConstructor()->getMock();
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            $subject = $this->getMockBuilder(ProtectValueWizard::class)
                ->onlyMethods(['translate'])
                ->getMock();
            $subject->setData($data);
        } else {
            $subject = $this->getMockBuilder(ProtectValueWizard::class)
                ->onlyMethods(['translate'])
                ->setConstructorArgs([$nodeFactory, $data])
                ->getMock();
        }

        $result = $subject->render();

        self::assertStringContainsString('data[foo][bar_protect][baz]', $result['html']);
    }
}
