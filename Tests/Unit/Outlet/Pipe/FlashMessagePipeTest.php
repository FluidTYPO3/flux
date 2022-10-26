<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * FlashMessagePipeTest
 */
class FlashMessagePipeTest extends AbstractPipeTestCase
{
    /**
     * @var array
     */
    protected $defaultData = array(
        'severity' => 0,
        'title' => 'test',
        'message' => 'test2',
        'storeInSession' => false
    );

    /**
     * @return void
     */
    public function setUp(): void
    {
        $GLOBALS['BE_USER'] = $this->getMockBuilder(BackendUserAuthentication::class)->disableOriginalConstructor()->getMock();
        $GLOBALS['TSFE'] = $this->getMockBuilder(TypoScriptFrontendController::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        unset($GLOBALS['BE_USER'], $GLOBALS['TSFE']);
    }

    protected function createInstance()
    {
        $flashMessageQueue = $this->getMockBuilder(FlashMessageQueue::class)->setMethods(['enqueue'])->disableOriginalConstructor()->getMock();
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(['getFlashMessageQueue'])->getMock();
        $instance->method('getFlashMessageQueue')->willReturn($flashMessageQueue);
        return $instance;
    }

    /**
     * @test
     */
    public function canGetAndSetSeverity()
    {
        $this->assertGetterAndSetterWorks('severity', 4, 4, true);
    }

    /**
     * @test
     */
    public function canGetAndSetTitle()
    {
        $this->assertGetterAndSetterWorks('title', 'test', 'test', true);
    }

    /**
     * @test
     */
    public function canGetAndSetMessage()
    {
        $this->assertGetterAndSetterWorks('message', 'test', 'test', true);
    }

    /**
     * @test
     */
    public function canGetAndSetStoreInSession()
    {
        $this->assertGetterAndSetterWorks('storeInSession', true, true, true);
    }
}
