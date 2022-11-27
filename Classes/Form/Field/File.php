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

class File extends AbstractMultiValueFormField
{
    protected ?string $disallowed = '';
    protected ?string $allowed = '';
    protected ?int $maxSize = null;
    protected ?string $uploadFolder = null;
    protected bool $showThumbnails = false;
    protected bool $useFalRelation = false;
    protected string $internalType = 'file_reference';
    protected ?string $renderType = null;

    public function buildConfiguration(): array
    {
        $configuration = $this->prepareConfiguration('group');
        $configuration['disallowed'] = $this->getDisallowed();
        $configuration['allowed'] = $this->getAllowed();
        $configuration['max_size'] = $this->getMaxSize();
        $configuration['uploadfolder'] = $this->getUploadFolder();
        $configuration['show_thumbs'] = $this->getShowThumbnails();
        $configuration['internal_type'] = $this->getInternalType();

        if ($this->getUseFalRelation() === true) {
            $configuration['internal_type'] = 'db';
            $configuration['allowed'] = 'sys_file';
            $configuration['appearance'] = [
                'elementBrowserAllowed' => $this->getAllowed() ? $this->getAllowed() : '*',
                'elementBrowserType' => 'file'
            ];
        }
        return $configuration;
    }

    public function setAllowed(?string $allowed): self
    {
        $this->allowed = $allowed;
        return $this;
    }

    public function getAllowed(): ?string
    {
        return $this->allowed;
    }

    public function setDisallowed(?string $disallowed): self
    {
        $this->disallowed = $disallowed;
        return $this;
    }

    public function getDisallowed(): ?string
    {
        return $this->disallowed;
    }

    public function setMaxSize(?int $maxSize): self
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    public function getMaxSize(): ?int
    {
        return $this->maxSize;
    }

    public function setUploadFolder(?string $uploadFolder): self
    {
        $this->uploadFolder = $uploadFolder;
        return $this;
    }

    public function getUploadFolder(): ?string
    {
        return $this->uploadFolder;
    }

    public function setShowThumbnails(bool $showThumbnails): self
    {
        $this->showThumbnails = $showThumbnails;
        return $this;
    }

    public function getShowThumbnails(): bool
    {
        return (boolean) $this->showThumbnails;
    }

    public function setUseFalRelation(bool $useFalRelation): self
    {
        $this->useFalRelation = $useFalRelation;
        return $this;
    }

    public function getUseFalRelation(): bool
    {
        return $this->useFalRelation;
    }

    public function getInternalType(): string
    {
        return $this->internalType;
    }

    public function setInternalType(string $internalType): self
    {
        $this->internalType = $internalType;
        return $this;
    }
}
