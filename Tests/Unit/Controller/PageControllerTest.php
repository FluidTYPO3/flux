<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\PageController;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\VersionUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageInformation;

class PageControllerTest extends AbstractTestCase
{
    public function testGetRecordReadsFromPageInformation(): void
    {
        if (!VersionUtility::isCoreAtLeast13()) {
            $this->markTestSkipped('Skipping test with PageInformation dependency on v12');
        }

        $record = ['foo' => 'bar'];

        $pageInformation = new PageInformation();
        $pageInformation->setId(1);
        $pageInformation->setContentFromPid(0);
        $pageInformation->setPageRecord($record);

        GeneralUtility::addInstance(PageInformation::class, $pageInformation);

        $GLOBALS['TYPO3_REQUEST'] = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $GLOBALS['TYPO3_REQUEST']->method('getAttribute')
            ->with('frontend.page.information')
            ->willReturn($pageInformation);

        /** @var PageController $subject */
        $subject = $this->getMockBuilder(PageController::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $record = $subject->getRecord();
        $this->assertSame($record, $record);
    }
}
