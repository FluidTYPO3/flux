<?php
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
     * field and sheets configuration. EXT:myext... syntax allowed
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getTemplatePathAndFilename(array $row);

    /**
     * Get an array of variables that should be used when rendering the
     * FlexForm configuration
     *
     * @param array $row The record which triggered the processing
     * @return array|NULL
     */
    public function getTemplateVariables(array $row);

    /**
     * Get the section name containing the FlexForm configuration. Return NULL
     * if no sections are used. If you use sections in your template, you MUST
     * use a section to contain the FlexForm configuration
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getConfigurationSectionName(array $row);

    /**
     * @param array|NULL $templateVariables
     * @return $this
     */
    public function setTemplateVariables($templateVariables);

    /**
     * @param string $templatePathAndFilename
     * @return $this
     */
    public function setTemplatePathAndFilename($templatePathAndFilename);

    /**
     * @param array|NULL $templatePaths
     * @return $this
     */
    public function setTemplatePaths($templatePaths);

    /**
     * @param string|NULL $configurationSectionName
     * @return $this
     */
    public function setConfigurationSectionName($configurationSectionName);
}
