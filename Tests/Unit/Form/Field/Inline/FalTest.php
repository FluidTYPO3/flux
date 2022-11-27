<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field\Inline;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\Form\Field\AbstractFieldTest;

class FalTest extends AbstractFieldTest
{
    protected array $chainProperties = array(
        'collapseAll' => false,
        'expandSingle' => false,
        'newRecordLinkAddTitle' => false,
        'newRecordLinkPosition' => Form::POSITION_TOP,
        'useCombination' => false,
        'useSortable' => false,
        'showPossibleLocalizationRecords' => false,
        'showRemovedLocalizationRecords' => false,
        'showAllLocalizationLink' => false,
        'showSynchronizationLink' => false,
        'enabledControls' => array(
            Form::CONTROL_INFO => false,
            Form::CONTROL_NEW => true,
            Form::CONTROL_DRAGDROP => true,
            Form::CONTROL_SORT => true,
            Form::CONTROL_HIDE => true,
            Form::CONTROL_DELETE => false,
            Form::CONTROL_LOCALISE => false,
        )
    );

    public function testBuildConfigurationWithCropVariants(): void
    {
        $subject = new Form\Field\Inline\Fal();
        $subject->setCropVariants(['foo' => []]);
        $output = $subject->buildConfiguration();
        self::assertSame(
            [
                'cropVariants' => [
                    'foo' => [],
                ],
            ],
            $output['overrideChildTca']['columns']['crop']['config']
        );
    }
}
