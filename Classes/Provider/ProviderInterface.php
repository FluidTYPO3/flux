<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\BasicProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\CommandProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\ContentTypeProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\PluginProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\PreviewProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProcessingProvider;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use FluidTYPO3\Flux\View\ViewContext;

/**
 * ProviderInterface
 */
interface ProviderInterface extends
    BasicProviderInterface,
    FormProviderInterface,
    GridProviderInterface,
    FluidProviderInterface,
    ControllerProviderInterface,
    RecordProviderInterface,
    DataStructureProviderInterface,
    ContentTypeProviderInterface,
    PluginProviderInterface,
    PreviewProviderInterface,
    RecordProcessingProvider
{
}
