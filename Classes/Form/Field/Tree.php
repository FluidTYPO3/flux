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

class Tree extends AbstractRelationFormField
{
    const DEFAULT_ALLOW_RECURSIVE_MODE = false;
    const DEFAULT_EXPAND_ALL = false;
    const DEFAULT_NON_SELECTABLE_LEVELS  = '0';
    const DEFAULT_MAX_LEVELS = 2;
    const DEFAULT_SHOW_HEADER = false;
    const DEFAULT_WIDTH = 280;

    protected ?string $parentField = null;
    protected bool $allowRecursiveMode = self::DEFAULT_ALLOW_RECURSIVE_MODE;
    protected bool $expandAll = self::DEFAULT_EXPAND_ALL;
    protected string $nonSelectableLevels = self::DEFAULT_NON_SELECTABLE_LEVELS;
    protected int $maxLevels = self::DEFAULT_MAX_LEVELS;
    protected bool $showHeader = self::DEFAULT_SHOW_HEADER;
    protected int $width = self::DEFAULT_WIDTH;

    public function buildConfiguration(): array
    {
        $configuration = $this->prepareConfiguration('select');
        $configuration['renderMode'] = 'tree';
        $configuration['renderType'] = 'selectTree';
        $configuration['treeConfig'] = [
            'parentField' => $this->getParentField(),
            'appearance' => [
                'allowRecursiveMode' => $this->getAllowRecursiveMode(),
                'expandAll' => $this->getExpandAll(),
                'nonSelectableLevels' => $this->getNonSelectableLevels(),
                'maxLevels' => $this->getMaxLevels(),
                'showHeader' => $this->getShowHeader(),
                'width' => $this->getWidth(),
            ],
        ];
        return $configuration;
    }

    public function setParentField(?string $parentField): self
    {
        $this->parentField = $parentField;
        return $this;
    }

    public function getParentField(): ?string
    {
        return $this->parentField;
    }

    public function setAllowRecursiveMode(bool $allowRecursiveMode): self
    {
        $this->allowRecursiveMode = $allowRecursiveMode;
        return $this;
    }

    public function getAllowRecursiveMode(): bool
    {
        return $this->allowRecursiveMode;
    }

    public function setExpandAll(bool $expandAll): self
    {
        $this->expandAll = $expandAll;
        return $this;
    }

    public function getExpandAll(): bool
    {
        return $this->expandAll;
    }

    public function setNonSelectableLevels(string $nonSelectableLevels): self
    {
        $this->nonSelectableLevels = $nonSelectableLevels;
        return $this;
    }

    public function getNonSelectableLevels(): string
    {
        return $this->nonSelectableLevels;
    }

    public function setMaxLevels(int $maxLevels): self
    {
        $this->maxLevels = $maxLevels;
        return $this;
    }

    public function getMaxLevels(): int
    {
        return $this->maxLevels;
    }

    public function setShowHeader(bool $showHeader): self
    {
        $this->showHeader = $showHeader;
        return $this;
    }

    public function getShowHeader(): bool
    {
        return $this->showHeader;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }
}
