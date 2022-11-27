<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class ColumnPosition extends UserFunction
{
    const FIELD_NAME = 'colPos';

    protected ?string $name = self::FIELD_NAME;

    public function buildConfiguration(): array
    {
        $fieldConfiguration = $this->prepareConfiguration('user');
        $fieldConfiguration['renderType'] = 'fluxColumnPosition';
        return $fieldConfiguration;
    }
}
