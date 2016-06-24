<?php
namespace FluidTYPO3\Flux\Tests\Unit;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * AbstractExceptionTestCase
 */
abstract class AbstractExceptionTestCase extends AbstractTestCase
{

    /**
     * @test
     */
    public function canBeCreatedUsingConstructor()
    {
        $message = 'message';
        $code = 123;
        $class = $this->createInstanceClassName();
        $instance = new $class($message, $code);
        $this->assertEquals($message, $instance->getMessage());
        $this->assertEquals($code, $instance->getCode());
    }

    /**
     * @test
     */
    public function supportsPreviousException()
    {
        $message = 'message';
        $code = 123;
        $previous = new \Exception('previous');
        $class = $this->createInstanceClassName();
        $instance = new $class($message, $code, $previous);
        $this->assertEquals($message, $instance->getMessage());
        $this->assertEquals($code, $instance->getCode());
        $this->assertSame($previous, $instance->getPrevious());
    }
}
