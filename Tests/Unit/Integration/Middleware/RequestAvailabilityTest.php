<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Middleware;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\MiddleWare\RequestAvailability;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestAvailabilityTest extends AbstractTestCase
{
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private RequestAvailability $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass();
        $this->handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMockForAbstractClass();
        $this->handler->method('handle')->willReturn(
            $this->getMockBuilder(ResponseInterface::class)->getMockForAbstractClass()
        );
        $this->subject = new RequestAvailability();
    }

    public function testSetsFromArgument(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        $this->subject->process($this->request, $this->handler);
        self::assertSame($this->request, $GLOBALS['TYPO3_REQUEST']);
    }

    public function testPreservesExistingGlobal(): void
    {
        $before = $GLOBALS['TYPO3_REQUEST'];
        $this->subject->process($this->request, $this->handler);
        self::assertSame($before, $GLOBALS['TYPO3_REQUEST']);
    }
}
