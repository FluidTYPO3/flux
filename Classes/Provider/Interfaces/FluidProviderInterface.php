<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Interface FluidProviderInterface
 *
 * Contract for Providers which are capable of interacting
 * with Fluid template files.
 */
interface FluidProviderInterface
{
    /**
     * Get the absolute path to the template file containing the FlexForm
     * field and sheets configuration. EXT:myext... syntax allowed.
     */
    public function getTemplatePathAndFilename(array $row): ?string;

    /**
     * Get an array of variables that should be used when rendering the
     * FlexForm configuration.
     */
    public function getTemplateVariables(array $row): array;

    /**
     * Get the section name containing the FlexForm configuration. Return NULL
     * if no sections are used. If you use sections in your template, you MUST
     * use a section to contain the FlexForm configuration.
     */
    public function getConfigurationSectionName(array $row): ?string;

    public function setTemplateVariables(?array $templateVariables): self;
    public function setTemplatePathAndFilename(?string $templatePathAndFilename): self;
    public function setTemplatePaths(?array $templatePaths): self;
    public function setConfigurationSectionName(?string $configurationSectionName): self;
}
