<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field\Inline;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\InlineFieldControls;
use FluidTYPO3\Flux\Enum\InlineFieldNewRecordButtonPosition;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\Form\Field\AbstractFieldTest;

class FalTest extends AbstractFieldTest
{
    protected array $chainProperties = [
        'collapseAll' => false,
        'expandSingle' => false,
        'newRecordLinkAddTitle' => false,
        'newRecordLinkPosition' => InlineFieldNewRecordButtonPosition::TOP,
        'useCombination' => false,
        'useSortable' => false,
        'showPossibleLocalizationRecords' => false,
        'showRemovedLocalizationRecords' => false,
        'showAllLocalizationLink' => false,
        'showSynchronizationLink' => false,
        'enabledControls' => [
            InlineFieldControls::INFO => false,
            InlineFieldControls::NEW => true,
            InlineFieldControls::DRAGDROP => true,
            InlineFieldControls::SORT => true,
            InlineFieldControls::HIDE => true,
            InlineFieldControls::DELETE => false,
            InlineFieldControls::LOCALIZE => false,
        ]
    ];

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
