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
 * Interface BasicProviderInterface
 *
 * Contract for Providers which support basic operability
 * like assigning settings, having an extension key context,
 * having a name and a priority.
 */
interface BasicProviderInterface
{
    public function loadSettings(array $settings): void;
    public function getExtensionKey(array $row): string;
    public function setExtensionKey(string $extensionKey): self;
    public function getName(): string;
    public function setName(string $name): self;
    public function getPriority(array $row): int;
}
