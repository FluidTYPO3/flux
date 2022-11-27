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
 * Interface ContentTypeProviderInterface
 *
 * Contract for Providers which handle integration of
 * new content types (based on templates or Forms and
 * usually combined with the controller provider interface).
 * Handles only types determined by `CType` in the DB.
 *
 * For plugins, see PluginProviderInterface
 */
interface ContentTypeProviderInterface
{
    public function setContentObjectType(string $contentObjectType): self;
    public function getContentObjectType(): string;
}
