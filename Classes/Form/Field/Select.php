<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractMultiValueFormField;

class Select extends AbstractMultiValueFormField
{
    /**
     * Displays option icons as table beneath the select.
     *
     * @see https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Select/Index.html#showicontable
     */
    protected bool $showIconTable = false;

    protected ?string $renderType = 'selectSingle';

    public function buildConfiguration(): array
    {
        $configuration = parent::prepareConfiguration('select');
        if ($this->getShowIconTable()) {
            $configuration['fieldWizard']['selectIcons']['disabled'] = false;
        }
        return $configuration;
    }

    public function getShowIconTable(): bool
    {
        return $this->showIconTable;
    }

    public function setShowIconTable(bool $showIconTable): self
    {
        $this->showIconTable = $showIconTable;
        return $this;
    }
}
