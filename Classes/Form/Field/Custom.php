<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\UserFunctions;

class Custom extends UserFunction
{
    protected ?\Closure $closure = null;

    public function buildConfiguration(): array
    {
        $fieldConfiguration = $this->prepareConfiguration('user');
        $fieldConfiguration['userFunc'] = UserFunctions::class . '->renderHtmlOutputField';
        $fieldConfiguration['renderType'] = 'fluxHtmlOutput';
        $fieldConfiguration['parameters'] = [
            'closure' => $this->getClosure(),
            'arguments' => $this->getArguments(),
        ];
        return $fieldConfiguration;
    }

    public function setClosure(\Closure $closure): self
    {
        $this->closure = $closure;
        return $this;
    }

    public function getClosure(): ?\Closure
    {
        return $this->closure;
    }
}
