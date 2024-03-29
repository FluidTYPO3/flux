<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;

/**
 * Public contract for objects that contains one content
 * type definition for a Flux-based content type.
 */
interface ContentTypeDefinitionInterface
{
    public function getContentTypeName(): string;
    public function getIconReference(): string;
    public function getExtensionIdentity(): string;
    public function getForm(array $record = []): Form;
    public function getGrid(array $record = []): ?Grid;
}
