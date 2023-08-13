<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\GridViewHelper;

class GridViewHelperTest extends AbstractViewHelperTestCase
{
    protected array $defaultArguments = [
        'name' => 'grid',
        'label' => 'Foo Bar',
        'variables' => [],
    ];

    public function testRenderStatic(): void
    {
        GridViewHelper::renderStatic($this->defaultArguments, function () { return ''; }, $this->renderingContext);

        self::assertNotEmpty(
            $this->viewHelperVariableContainer->get(GridViewHelper::SCOPE, GridViewHelper::SCOPE_VARIABLE_GRIDS)
        );
    }
}
