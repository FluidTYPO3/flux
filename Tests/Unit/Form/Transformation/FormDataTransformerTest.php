<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\DataTransformerRegistry;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Form\Transformation\Transformer\ArrayTransformer;
use FluidTYPO3\Flux\Form\Transformation\Transformer\BooleanTransformer;
use FluidTYPO3\Flux\Form\Transformation\Transformer\FloatTransformer;
use FluidTYPO3\Flux\Form\Transformation\Transformer\IntegerTransformer;
use FluidTYPO3\Flux\Form\Transformation\Transformer\ObjectTransformer;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\VersionUtility;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\Service\FlexFormService;

class FormDataTransformerTest extends AbstractTestCase
{
    private FlexFormService $flexFormService;
    private DataTransformerRegistry $registry;
    private ?FormDataTransformer $subject = null;

    protected function setUp(): void
    {
        $serviceLocator = $this->getMockBuilder(ServiceLocator::class)
            ->onlyMethods(['getProvidedServices', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->method('getProvidedServices')->willReturn(
            [
                'flux.datatransformer.array' => ArrayTransformer::class,
                'flux.datatransformer.boolean' => BooleanTransformer::class,
                'flux.datatransformer.integer' => IntegerTransformer::class,
                'flux.datatransformer.float' => FloatTransformer::class,
                'flux.datatransformer.object' => ObjectTransformer::class,
            ]
        );
        $serviceLocator->method('get')->willReturnMap(
            [
                ['flux.datatransformer.array', new ArrayTransformer()],
                ['flux.datatransformer.boolean', new BooleanTransformer()],
                ['flux.datatransformer.integer', new IntegerTransformer()],
                ['flux.datatransformer.float', new FloatTransformer()],
                ['flux.datatransformer.object', new ObjectTransformer()],
            ]
        );

        if (VersionUtility::isCoreAtLeast14()) {
            $this->flexFormService = (new \ReflectionClass(FlexFormService::class))->newInstanceWithoutConstructor();
        } else {
            $this->flexFormService = $this->getMockBuilder(FlexFormService::class)
                ->onlyMethods(['convertFlexFormContentToArray'])
                ->disableOriginalConstructor()
                ->getMock();
        }


        $this->registry = new DataTransformerRegistry($serviceLocator);
        $this->subject = new FormDataTransformer($this->flexFormService, $this->registry);

        parent::setUp();
    }

    private function getConstructorArguments(): array
    {
        return [
            $this->flexFormService,
            $this->registry,
        ];
    }

    public function fixtureTransformToFooString(): string
    {
        return 'foo';
    }

    /**
     * @dataProvider getConvertFlexFormContentToArrayTestValues
     */
    public function testConvertFlexFormContentToArray(
        string|FlexFormFieldValues $flexFormContent,
        ?Form $form,
        ?string $languagePointer,
        ?string $valuePointer,
        array $expected
    ) : void {
        if (VersionUtility::isCoreBelow14()) {
            $this->flexFormService->method('convertFlexFormContentToArray')->willReturn($expected);
        }

        $instance = new FormDataTransformer(...$this->getConstructorArguments());

        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    public function getConvertFlexFormContentToArrayTestValues(): array
    {
        $emptyValue = '';
        $filledValue = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
        if (VersionUtility::isCoreAtLeast14()) {
            $emptyValue = new FlexFormFieldValues([]);
            $filledValue = new FlexFormFieldValues(['settings' => ['input' => '0']]);
        }

        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        return [
            [$emptyValue, null, '', '', []],
            [$emptyValue, $form, '', '', []],
            [$filledValue, $form, '', '', ['settings' => ['input' => 0]]]
        ];
    }

    public function testConvertFlexFormContentToArrayWithTransform(): void
    {
        $expected = [
            'foo' => 'bar',
        ];

        $flexFormContent = 'abc';
        if (VersionUtility::isCoreAtLeast14()) {
            $flexFormContent = new FlexFormFieldValues(['foo' => 'bar']);
        }
        $languagePointer = null;
        $valuePointer = null;

        $form = Form::create();
        $form->setOption(FormOption::TRANSFORM, true);

        if (VersionUtility::isCoreBelow14()) {
            $this->flexFormService->method('convertFlexFormContentToArray')->willReturn($expected);
        }

        $instance = $this->getMockBuilder(FormDataTransformer::class)
            ->onlyMethods(['transformAccordingToConfiguration'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->method('transformAccordingToConfiguration')->willReturnArgument(0);

        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getValuesAndTransformations
     * @param mixed $value
     * @param mixed $expected
     */
    public function testTransformation($value, string $transformation, $expected): void
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField(Form\Field\Input::class, 'field')->setTransform($transformation);
        $transformed = $this->subject->transformAccordingToConfiguration(['field' => $value], $form);
        $this->assertNotSame(
            $expected,
            $transformed,
            'Transformation type ' . $transformation . ' failed; values are still identical'
        );
    }

    public function getValuesAndTransformations(): array
    {
        return [
            [['1', '2', '3'], 'integer', [1, 2, 3]],
            ['0', 'integer', 0],
            ['0.12', 'float', 0.12],
            ['1,2,3', 'array', [1, 2, 3]],
            ['1', 'boolean', true],
        ];
    }

    public function testTransformationSectionObject(): void
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $section = $form->createContainer(Form\Container\Section::class, 'section');
        $object = $section->createContainer(Form\Container\SectionObject::class, 'object');
        $object->setTransform(\ArrayObject::class);
        $object->createField(Form\Field\Input::class, 'foo');
        $object->createField(Form\Field\Input::class, 'baz');

        $data = ['section' => ['abcdef123456' => ['object' => ['foo' => 'bar', 'baz' => 'test']]]];

        $transformed = $this->subject->transformAccordingToConfiguration($data, $form);
        $expected = $transformed;
        $expected['section']['abcdef123456']['object'] = new \ArrayObject(
            $expected['section']['abcdef123456']['object']
        );

        self::assertEquals($expected, $transformed);
    }
}
