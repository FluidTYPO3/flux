<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\UserFunctions;

/**
 * ColumnPosition
 */
class ColumnPosition extends UserFunction
{
    const FIELD_NAME = 'colPos';

    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var string
     */
    protected $name = self::FIELD_NAME;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $fieldConfiguration = $this->prepareConfiguration('user');
        $fieldConfiguration['userFunc'] = UserFunctions::class . '->renderColumnPositionField';
        return $fieldConfiguration;
    }
}
