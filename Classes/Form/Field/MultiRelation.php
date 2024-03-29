<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractRelationFormField;

class MultiRelation extends AbstractRelationFormField
{
    protected ?string $renderType = null;

    public function buildConfiguration(): array
    {
        $configuration = $this->prepareConfiguration('group');
        $configuration['internal_type'] = 'db';
        $configuration['allowed'] = $this->getTable();
        return $configuration;
    }
}
