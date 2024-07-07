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
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
            ]
        );
        $serviceLocator->method('get')->willReturnMap(
            [
                ['flux.datatransformer.array', new ArrayTransformer()],
                ['flux.datatransformer.boolean', new BooleanTransformer()],
                ['flux.datatransformer.integer', new IntegerTransformer()],
                ['flux.datatransformer.float', new FloatTransformer()],
            ]
        );

        $this->flexFormService = $this->getMockBuilder(FlexFormService::class)
            ->onlyMethods(['convertFlexFormContentToArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(DataTransformerRegistry::class)
            ->setConstructorArgs([$serviceLocator])
            ->getMock();

        $this->subject = $this->getMockBuilder(FormDataTransformer::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();

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
        string $flexFormContent,
        ?Form $form,
        ?string $languagePointer,
        ?string $valuePointer,
        array $expected
    ) : void {
        $this->flexFormService->method('convertFlexFormContentToArray')->willReturn($expected);
        $instance = new FormDataTransformer(...$this->getConstructorArguments());

        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    public function getConvertFlexFormContentToArrayTestValues(): array
    {
        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        return [
            ['', null, '', '', []],
            ['', $form, '', '', []],
            [Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD, $form, '', '', ['settings' => ['input' => 0]]]
        ];
    }

    public function testConvertFlexFormContentToArrayWithTransform(): void
    {
        $expected = [
            'foo' => 'bar',
        ];

        $flexFormContent = 'abc';
        $languagePointer = null;
        $valuePointer = null;

        $form = Form::create();
        $form->setOption(FormOption::TRANSFORM, true);

        $this->flexFormService->method('convertFlexFormContentToArray')->willReturn($expected);

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
            ['123,321', 'InvalidClass', '123'],
            ['1', 'boolean', true],
            /*
            [date('Ymd'), 'DateTime', new \DateTime(date('Ymd'))],
            ['1,2', ObjectStorage::class . '<\\Invalid>', null],
            ['bar', self::class . '->fixtureTransformToFooString', 'foo'],
            */
        ];
    }
}
