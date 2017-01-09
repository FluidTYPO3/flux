<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * AddTest
 */
class AddTest extends AbstractWizardTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'test',
        'label' => 'Test field',
        'hideParent' => false,
        'table' => 'tt_content',
        'storagePageUid' => 1,
        'setValue' => false,
    );
}
