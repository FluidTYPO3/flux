<?php
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
    /**
     * @param array $settings
     * @return void
     */
    public function loadSettings(array $settings);

    /**
     * Return the extension key this processor belongs to
     *
     * @param array $row The record which triggered the processing
     * @return string
     */
    public function getExtensionKey(array $row);

    /**
     * @param string $extensionKey
     * @return $this
     */
    public function setExtensionKey($extensionKey);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @abstract
     * @param array $row The record data. Changing fields' values changes the record's values before display
     * @return integer
     */
    public function getPriority(array $row);
}
