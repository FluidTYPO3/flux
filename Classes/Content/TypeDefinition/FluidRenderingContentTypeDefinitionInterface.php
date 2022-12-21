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
 * Public contract for content types that are
 * capable of rendering Fluid templates.
 */
interface FluidRenderingContentTypeDefinitionInterface extends ContentTypeDefinitionInterface
{
    public function isUsingTemplateFile(): bool;
    public function isUsingGeneratedTemplateSource(): bool;
    public function getTemplatePathAndFilename(): string;
    public function getProviderClassName(): ?string;
}
