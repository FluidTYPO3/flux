<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;

class InlineViewHelperTest extends AbstractViewHelperTestCase
{
    public function testRendersInlineCodeFromArgument(): void
    {
        $arguments = [
            'code' => 'Value: {variable}',
        ];
        $output = $this->executeViewHelper($arguments, ['variable' => 'test']);
        self::assertSame('Value: test', $output);
    }

    public function testRendersInlineCodeFromChild(): void
    {
        $arguments = [];
        $output = $this->executeViewHelper($arguments, ['variable' => 'test'], new TextNode('Value: {variable}'));
        self::assertSame('Value: test', $output);
    }
}
