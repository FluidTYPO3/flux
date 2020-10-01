<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Abstract Pipe
 *
 * Base class for all Pipes
 */
abstract class AbstractPipe implements PipeInterface
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function conduct($data)
    {
        return $data;
    }
}
