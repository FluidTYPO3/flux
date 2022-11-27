<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractWizard;

/**
 * Add wizard
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by properties.
 *
 * @deprecated Will be removed in Flux 10.0
 */
class Add extends AbstractWizard
{
    protected ?string $name = 'add';
    protected ?string $type = 'script';
    protected ?string $icon = 'add.gif';
    protected array $module = [
        'name' => 'wizard_add'
    ];
    protected string $table = '';
    protected int $storagePageUid = 0;
    protected ?string $setValue = null;

    public function buildConfiguration(): array
    {
        $configuration = [
            'params' => [
                'table' => $this->getTable(),
                'pid' => $this->getStoragePageUid(),
                'setValue' => $this->getSetValue()
            ]
        ];
        return $configuration;
    }

    public function setSetValue(?string $setValue): self
    {
        $this->setValue = $setValue;
        return $this;
    }

    public function getSetValue(): ?string
    {
        return $this->setValue;
    }

    public function setStoragePageUid(int $storagePageUid): self
    {
        $this->storagePageUid = $storagePageUid;
        return $this;
    }

    public function getStoragePageUid(): int
    {
        return $this->storagePageUid;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
