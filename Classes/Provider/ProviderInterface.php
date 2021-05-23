<?php
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\CommandProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\ContentTypeProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\PluginProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\PreviewProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use FluidTYPO3\Flux\View\ViewContext;

/**
 * ProviderInterface
 */
interface ProviderInterface extends
    FormProviderInterface,
    GridProviderInterface,
    FluidProviderInterface,
    ControllerProviderInterface,
    RecordProviderInterface,
    DataStructureProviderInterface,
    CommandProviderInterface,
    ContentTypeProviderInterface,
    PluginProviderInterface,
    PreviewProviderInterface
{
    /**
     * @param array $settings
     * @return void
     */
    public function loadSettings(array $settings);

    /**
     * Return the extension key this processor belongs to
     *
     * @param array $row The record which triggered the processing
     * @return string
     */
    public function getExtensionKey(array $row);

    /**
     * @param string $extensionKey
     * @return ProviderInterface
     */
    public function setExtensionKey($extensionKey);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @abstract
     * @param array $row The record data. Changing fields' values changes the record's values before display
     * @return integer
     */
    public function getPriority(array $row);
}
