<?php
namespace FluidTYPO3\Flux\Tests\Unit\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * NoTemplateTest
 */
class NoTemplateTest extends AbstractUserFunctionTest
{

    /**
     * @var boolean
     */
    protected $expectsNull = true;

    /**
     * @test
     */
    public function rendersOutputWithDebugEnabled()
    {
        $backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 1;
        $this->expectsNull = false;
        $this->canCallMethodAndReceiveOutput();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
    }
}
