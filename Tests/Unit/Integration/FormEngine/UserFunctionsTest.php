<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\UserFunctions;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class UserFunctionsTest extends AbstractTestCase
{
    /**
     * @param string $method
     * @param array $parameters
     * @param boolean $expectsNull
     * @test
     * @dataProvider getUserFunctionTestValues
     */
    public function canCallMethodAndReceiveOutput($method, array $parameters, $expectsNull)
    {
        $reference = $this->getMockBuilder(UserFunctions::class)->getMock();
        $subject = $this->getMockBuilder(UserFunctions::class)->setMethods(['translate'])->getMock();
        $subject->method('translate')->willReturnArgument(0);
        $output = $subject->$method($parameters, $reference);
        if ($expectsNull) {
            $this->assertNull($output);
        } else {
            $this->assertNotEmpty($output);
        }
    }

    public function getUserFunctionTestValues()
    {
        return [
            'clear value field' => [
                'renderClearValueWizardField',
                ['itemName' => 'data[tt_content][1][pi_flexform][data][options][lDEF][settings.distribution][vDEF]'],
                false
            ],
            'HTML output field' => [
                'renderHtmlOutputField',
                ['parameters' => ['closure' => function () {
                    return 'test';
                }]],
                false
            ],
        ];
    }
}
