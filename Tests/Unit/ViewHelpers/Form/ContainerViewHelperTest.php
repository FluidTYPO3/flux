<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

/**
 * ContainerViewHelperTest
 */
class ContainerViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @test
     */
    public function canExecuteViewHelper()
    {
        $arguments = array(
            'name' => 'test',
            'label' => 'Test container'
        );
        $result = $this->executeViewHelper($arguments);
        $this->assertEmpty(trim($result));
    }
}
