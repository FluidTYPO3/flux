<?php
namespace FluidTYPO3\Flux\Proxy;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Imaging\IconState;

/**
 * Final/readonly classes is a great way to give all your developer community users the middle finger.
 *
 * @codeCoverageIgnore
 */
class IconFactoryProxy
{
    private IconFactory $iconFactory;

    public function __construct(IconFactory $iconFactory)
    {
        $this->iconFactory = $iconFactory;
    }

    /**
     * @param string|IconSize $size
     * @param \TYPO3\CMS\Core\Type\Icon\IconState|IconState|null $state
     */
    public function getIcon(
        string $identifier,
        $size,
        ?string $overlayIdentifier = null,
        $state = null
    ): Icon {
        return $this->iconFactory->getIcon($identifier, $size, $overlayIdentifier, $state);
    }
}
