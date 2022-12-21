<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Serialize Safe Interface (Content Type Definition)
 *
 * Signaling interface declaring that the type definition
 * implementing the interface can be safely serialized.
 *
 * Accompanying trait provides trigger methods to ensure
 * that form and grid have been thawed, if they were
 * initially in a serialized format (e.g. flex form XML)
 */
interface SerializeSafeInterface
{
    public function __sleep();
}
