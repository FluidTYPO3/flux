<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class Text extends Input implements FieldInterface
{
    protected int $columns = 85;
    protected int $rows = 10;
    protected bool $enableRichText = false;
    protected string $richtextConfiguration = '';
    protected ?string $renderType = null;
    protected string $format = '';
    protected ?string $placeholder = null;

    public function buildConfiguration(): array
    {
        $configuration = $this->prepareConfiguration('text');
        $configuration['rows'] = $this->getRows();
        $configuration['cols'] = $this->getColumns();
        $configuration['eval'] = $this->getValidate();
        $configuration['placeholder'] = $this->getPlaceholder();
        if ($this->getEnableRichText()) {
            $configuration['enableRichtext'] = true;
            $configuration['softref'] = 'typolink_tag,email[subst],url';
            $configuration['richtextConfiguration'] = $this->getRichtextConfiguration();
        }
        $renderType = $this->getRenderType();
        if (!empty($renderType)) {
            $configuration['renderType'] = $renderType;
            $configuration['format'] = $this->getFormat();
        }
        return $configuration;
    }

    public function setColumns(int $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function getColumns(): int
    {
        return $this->columns;
    }

    public function setEnableRichText(bool $enableRichText): self
    {
        $this->enableRichText = $enableRichText;
        return $this;
    }

    public function getEnableRichText(): bool
    {
        return $this->enableRichText;
    }

    public function setRows(int $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function getRenderType(): ?string
    {
        return $this->renderType;
    }

    public function setRenderType(?string $renderType): self
    {
        $this->renderType = $renderType;
        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    public function setPlaceholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * Fetch richtext editor configuration preset
     *
     * The following places are looked at:
     *
     * 1. 'richtextConfiguration' attribute of the current tag
     * 2. PageTSconfig: "RTE.tx_flux.preset"
     * 3. PageTSconfig: "RTE.default.preset"
     */
    public function getRichtextConfiguration(): string
    {
        return $this->richtextConfiguration ?: $this->getPageTsConfigForRichTextEditor();
    }

    protected function getPageTsConfigForRichTextEditor(): string
    {
        $pageUid = 0;
        $root = $this->getRoot();
        if ($root instanceof Form) {
            /** @var array|null $record */
            $record = $root->getOption('record');
            if ($record !== null) {
                $pageUid = (integer) ($record['pid'] ?? 0);
            }
        }

        return $this->fetchPageTsConfig($pageUid)['RTE.']['default.']['preset'] ?? 'default';
    }

    public function setRichtextConfiguration(string $richtextConfiguration): self
    {
        $this->richtextConfiguration = $richtextConfiguration;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchPageTsConfig(int $pageUid): array
    {
        return BackendUtility::getPagesTSconfig($pageUid);
    }
}
