<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Field\Text;

class TextTest extends InputTest
{
    protected array $chainProperties = [
        'name' => 'test',
        'label' => 'Test field',
        'enabled' => true,
        'maxCharacters' => 30,
        'maximum' => 10,
        'minimum' => 0,
        'validate' => 'trim,int',
        'default' => 'test',
        'columns' => 85,
        'rows' => 8,
        'requestUpdate' => true,
        'format' => 'html',
    ];

    /**
     * @test
     */
    public function canChainSetterForEnableRichText()
    {
        /** @var Text $instance */
        $instance = $this->createInstance();
        $chained = $instance->setEnableRichText(true);
        $this->assertSame($instance, $chained);
        $this->assertTrue($instance->getEnableRichText());
    }

    public function testBuildConfigurationWithRteResolving(): void
    {
        $subject = $this->getMockBuilder(Text::class)
            ->setMethods(['fetchPageTsConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())->method('fetchPageTsConfig')->with(123)->willReturn([]);
        $subject->setEnableRichText(true);
        $subject->setRenderType('rte');

        $form = Form::create();
        $form->setOption(FormOption::RECORD, ['pid' => 123]);
        $form->add($subject);

        $expected = [
            'type' => 'text',
            'transform' => null,
            'default' => null,
            'rows' => 10,
            'cols' => 85,
            'eval' => 'trim',
            'placeholder' => null,
            'enableRichtext' => true,
            'softref' => 'typolink_tag,images,email[subst],url',
            'richtextConfiguration' => 'default',
            'renderType' => 'rte',
            'format' => '',
        ];
        $output = $subject->buildConfiguration();
        self::assertSame($expected, $output);
    }
}
