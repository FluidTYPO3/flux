<?php
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Interface PreviewProviderInterface
 *
 * Contract for Providers which generate previews.
 */
interface PreviewProviderInterface
{
    /**
     * Returns [$header, $content) preview chunks
     *
     * @abstract
     * @param array $row The record data to be analysed for variables to use in a rendered preview
     * @return array
     */
    public function getPreview(array $row);
}
