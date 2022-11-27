<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Fluidpages project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\SubPageProvider;

/**
 * Class DummyPageProvider
 */
class DummyPageProvider extends SubPageProvider
{
    protected array $values = [];

    public function trigger(array $row, ?string $table, ?string $field, ?string $extensionKey = null): bool
    {
        return true;
    }

    public function setFlexFormValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    public function getFlexFormValues(array $row): array
    {
        return [];
    }

    public function getForm(array $row): ?Form
    {
        return $this->form;
    }
}
