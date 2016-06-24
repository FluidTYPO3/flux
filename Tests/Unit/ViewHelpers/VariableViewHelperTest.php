<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

/**
 * VariableViewHelperTest
 */
class VariableViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @test
     */
    public function canFetchTemplateVariable()
    {
        $arguments = array('name' => 'foobar');
        $variables = array('foobar' => 'Hello world!');
        $output = $this->executeViewHelper($arguments, $variables);
        $this->assertSame($output, $variables['foobar']);
    }

    /**
     * @test
     */
    public function canFetchNestedTemplateVariable()
    {
        $arguments = array('name' => 'nested.nested');
        $variables = array('nested' => array('nested' => 'Hello again world!'));
        $output = $this->executeViewHelper($arguments, $variables);
        $this->assertSame($output, $variables['nested']['nested']);
    }
}
