<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\NormalizedDataConfigurationProvider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class NormalizedDataConfigurationProviderTest extends AbstractTestCase
{
    private array $dummyParameters = [
        'tableName' => 'not_matched',
        'databaseRow' => [
            'field_options' => null,
            'field_label' => 'Original label',
        ],
        'processedTca' => [
            'columns' => [
                'field_value' => [
                    'config' => [

                    ],
                ],
            ],
        ],
    ];

    /**
     * @dataProvider getAddDataTestValues
     */
    public function testAddData(array $expected, array $options): void
    {
        $subject = new NormalizedDataConfigurationProvider();
        self::assertSame($expected, $subject->addData($options));
    }

    public function getAddDataTestValues(): array
    {
        $defaultLabel = $this->dummyParameters['databaseRow']['field_label'];

        $matchedWithInvalidOptions = $this->dummyParameters;
        $matchedWithInvalidOptions['tableName'] = 'flux_field';
        $matchedWithInvalidOptions['databaseRow']['field_options'] = '{"invalid": "json}';
        $matchedWithInvalidOptions['processedTca']['columns']['field_value']['label'] = $defaultLabel;
        $matchedWithInvalidOptions['processedTca']['columns']['field_value']['config'] = ['type' => 'passthrough'];

        $matchedWithInvalidOptionsExpectation = $matchedWithInvalidOptions;
        $matchedWithInvalidOptionsExpectation['processedTca']['columns']['field_value']['config']
            = ['type' => 'passthrough'];

        $matchedWithOptionsWithoutLabel = $this->dummyParameters;
        $matchedWithOptionsWithoutLabel['tableName'] = 'flux_field';
        $matchedWithOptionsWithoutLabel['databaseRow']['field_options'] = json_encode(['foo' => 'bar']);
        $matchedWithOptionsWithoutLabel['processedTca']['columns']['field_value']['label'] = $defaultLabel;
        $matchedWithOptionsWithoutLabel['processedTca']['columns']['field_value']['config'] = ['foo' => 'bar'];

        $matchedWithOptionsWithoutLabelExpectation = $matchedWithOptionsWithoutLabel;

        $matchedWithOptionsWithLabel = $matchedWithOptionsWithoutLabel;
        $matchedWithOptionsWithLabel['databaseRow']['field_label'] = 'Custom label';

        $matchedWithOptionsWithLabelExpectation = $matchedWithOptionsWithLabel;
        $matchedWithOptionsWithLabelExpectation['processedTca']['columns']['field_value']['label'] = 'Custom label';

        return [
            'with unmatched field' => [
                $this->dummyParameters,
                $this->dummyParameters
            ],
            'with matched field without options' => [
                $matchedWithInvalidOptionsExpectation,
                $matchedWithInvalidOptions,
            ],
            'with matched field with options without label' => [
                $matchedWithOptionsWithoutLabelExpectation,
                $matchedWithOptionsWithoutLabel,
            ],
            'with matched field with options with label' => [
                $matchedWithOptionsWithLabelExpectation,
                $matchedWithOptionsWithLabel,
            ],
        ];
    }
}
