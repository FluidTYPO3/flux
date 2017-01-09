<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * Pipe Interface
 *
 * Interface for Pipes which process data for Outlets.
 */
interface PipeInterface
{

    /**
     * @param array $settings
     * @return void
     */
    public function loadSettings(array $settings);

    /**
     * Accept $data and do whatever the Pipe should do before
     * returning the same or a modified version of $data for
     * chaining with other potential Pipes.
     *
     * @param mixed $data
     * @return mixed
     */
    public function conduct($data);

    /**
     * Get a human-readable name of this Pipe.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Return the FormComponent "Field" instances which represent
     * options this Pipe supports.
     *
     * @return FieldInterface[]
     */
    public function getFormFields();
}
