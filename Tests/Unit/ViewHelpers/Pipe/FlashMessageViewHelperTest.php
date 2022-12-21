<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

class FlashMessageViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @dataProvider getTestArguments
     */
    public function testWithArguments(array $arguments): void
    {
        $result = $this->executeViewHelper($arguments, [], null, null, 'FakePlugin');
        $this->assertSame('', $result);
    }

    public function getTestArguments(): array
    {
        return array(
            array(array()),
            array(array('message' => 'Some message')),
            array(array('message' => 'Some message', 'title' => 'Some title')),
            array(array('message' => 'Some message', 'title' => 'Some title', 'storeInSession' => true)),
        );
    }
}
