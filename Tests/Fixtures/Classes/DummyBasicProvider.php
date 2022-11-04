<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\BasicProviderInterface;

class DummyBasicProvider implements BasicProviderInterface
{
    public function loadSettings(array $settings)
    {
    }

    public function getExtensionKey(array $row)
    {
        return 'ext';
    }

    public function setExtensionKey($extensionKey)
    {
    }

    public function getName()
    {
        return 'foo';
    }

    public function setName($name)
    {
    }

    public function getPriority(array $row)
    {
        return 1;
    }
}
