<?php
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Page SubConfiguration Provider
 *
 * This Provider has a slightly lower priority
 * than the main PageProvider but will trigger
 * on any selection in the targeted field,
 * including when "parent decides" is selected.
 *
 * This lets the PageProvider act on records
 * that define a specific action to use and the
 * SubPageProvider act on all other page records.
 */
class SubPageProvider extends PageProvider implements ProviderInterface
{

    /**
     * @var string
     */
    protected $fieldName = self::FIELD_NAME_SUB;
}
