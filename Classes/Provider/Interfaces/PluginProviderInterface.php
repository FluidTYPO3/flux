<?php
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Interface PluginProviderInterface
 *
 * Contract for Providers which handle integration of
 * plugins with plugin type set in `list_type`
 * (as opposed to in `CType`).
 *
 * For CType, see ContentTypeProviderInterface
 */
interface PluginProviderInterface
{
    /**
     * @param string $listType
     */
    public function setListType($listType);

    /**
     * @return string
     */
    public function getListType();
}
