<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Attribute;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

#[\Attribute(\Attribute::TARGET_CLASS)]
class DataTransformer
{
    public const TAG_NAME = 'flux.datatransformer';
    public string $identifier;

    public function __construct(
        string $identifier
    ) {
        $this->identifier = $identifier;
    }
}
