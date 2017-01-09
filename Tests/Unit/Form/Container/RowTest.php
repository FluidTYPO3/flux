<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * RowTest
 */
class RowTest extends AbstractContainerTest
{

    /**
     * @test
     */
    public function canUseGetColumnsMethod()
    {
        /** @var Row $instance */
        $instance = $this->createInstance();
        $this->assertEmpty($instance->getColumns());
    }

    /**
     * Override: this Component does not support LLL rewriting
     * and must skip this test which it otherwise inherits
     *
     * @disabledtest
     */
    public function canAutoWriteLabel()
    {

    }

    /**
     * Override: this Component does not support LLL rewriting
     * and must skip this test which it otherwise inherits
     *
     * @disabledtest
     */
    public function canUseShorthandLanguageLabel()
    {

    }
}
