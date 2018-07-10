<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * FileTest
 */
class FileTest extends AbstractFieldTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'test',
        'label' => 'Test field',
        'enabled' => true,
        'maxSize' => 135153542,
        'allowed' => 'jpg,gif',
        'disallowed' => 'doc,docx',
        'uploadFolder' => '',
        'showThumbnails' => true
    );
}
