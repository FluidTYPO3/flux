<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * FormDataTransformerTest
 */
class FormDataTransformerTest extends AbstractTestCase
{
    private FileRepository $fileRepository;
    private FlexFormService $flexFormService;
    private ?FrontendUserRepository $frontendUserRepository = null;
    private ?FrontendUser $frontendUser = null;
    private ?FormDataTransformer $subject = null;

    protected function setUp(): void
    {
        $this->singletonInstances[Repository::class] = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileRepository = $this->getMockBuilder(FileRepository::class)
            ->onlyMethods(['findByRelation'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->flexFormService = $this->getMockBuilder(FlexFormService::class)
            ->onlyMethods(['convertFlexFormContentToArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->getMockBuilder(FormDataTransformer::class)
            ->onlyMethods(['loadObjectsFromRepository'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();

        parent::setUp();
    }

    private function initializeFrontendUserFixtures(): void
    {
        $this->frontendUser = $this->getMockBuilder(FrontendUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->frontendUserRepository = $this->getMockBuilder(FrontendUserRepository::class)
            ->setMethods(['findByUid'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontendUserRepository->method('findByUid')->willReturn($this->frontendUser);

        GeneralUtility::setSingletonInstance(FrontendUserRepository::class, $this->frontendUserRepository);
    }

    private function getConstructorArguments(): array
    {
        return [
            $this->fileRepository,
            $this->flexFormService,
        ];
    }

    public function fixtureTransformToFooString(): string
    {
        return 'foo';
    }


    /**
     * @test
     * @dataProvider getConvertFlexFormContentToArrayTestValues
     * @param string $flexFormContent
     * @param Form|NULL $form
     * @param string|NULL $languagePointer
     * @param string|NULL $valuePointer
     * @param array $expected
     */
    public function testConvertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer, $expected)
    {
        $this->flexFormService->method('convertFlexFormContentToArray')->willReturn($expected);
        $instance = new FormDataTransformer(...$this->getConstructorArguments());

        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getConvertFlexFormContentToArrayTestValues()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
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
        $form->setOption(Form::OPTION_TRANSFORM, true);

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
        $this->subject->method('loadObjectsFromRepository')->willReturn([]);
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
            [date('Ymd'), 'DateTime', new \DateTime(date('Ymd'))],
            ['1', 'boolean', true],
            ['1,2', ObjectStorage::class . '<\\Invalid>', null],
            ['bar', self::class . '->fixtureTransformToFooString', 'foo'],
        ];
    }

    /**
     * @dataProvider getValuesAndTransformationsForDomainObjects
     * @param mixed $value
     * @param mixed $expected
     */
    public function testTransformationOfDomainObjects($value, string $transformation, $expected): void
    {
        if (!class_exists(FrontendUser::class)) {
            $this->markTestSkipped('Skipping test with FrontendUser dependency');
        }

        $this->initializeFrontendUserFixtures();
        $this->subject->method('loadObjectsFromRepository')->willReturn([]);
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField(Form\Field\Input::class, 'field')->setTransform($transformation);
        $transformed = $this->subject->transformAccordingToConfiguration(['field' => $value], $form);
        $this->assertNotSame(
            $expected,
            $transformed,
            'Transformation type ' . $transformation . ' failed; values are still identical'
        );
    }

    public function getValuesAndTransformationsForDomainObjects(): array
    {
        return [
            ['1,2', ObjectStorage::class . '<' . FrontendUser::class . '>', null],
            ['1', FrontendUser::class, $this->frontendUser],
        ];
    }

    /**
     * @dataProvider getTransformWithFileTargetTypesTestValues
     * @param mixed $expected
     */
    public function testTransformationWithFileTargetTypes(string $type, array $files, $expected): void
    {
        $this->fileRepository->method('findByRelation')->willReturn($files);

        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->setOption(Form::OPTION_RECORD_TABLE, 'tt_content');
        $form->setOption(Form::OPTION_RECORD, ['uid' => 1]);

        $form->createField(Form\Field\Input::class, 'field')->setTransform($type);
        $transformed = $this->subject->transformAccordingToConfiguration(['field' => '1'], $form);

        self::assertSame(['field' => $expected], $transformed);
    }

    public function getTransformWithFileTargetTypesTestValues(): array
    {
        $file1 = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $fileReference1 = $this->getMockBuilder(FileReference::class)
            ->onlyMethods(['getOriginalFile'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileReference1->method('getOriginalFile')->willReturn($file1);

        $file2 = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $fileReference2 = $this->getMockBuilder(FileReference::class)
            ->onlyMethods(['getOriginalFile'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileReference2->method('getOriginalFile')->willReturn($file2);

        return [
            'file, non-empty' => ['file', [$fileReference1], $file1],
            'files, non-empty' => ['files', [$fileReference1, $fileReference2], [$file1, $file2]],
            'filereference, non-empty' => ['filereference', [$fileReference1], $fileReference1],
            'filesreferences, non-empty' => [
                'filereferences',
                [$fileReference1, $fileReference2],
                [$fileReference1, $fileReference2]
            ],
            'file, empty' => ['file', [], null],
            'files, empty' => ['files', [], []],
            'filereference, empty' => ['filereference', [], null],
            'filereferences, empty' => ['filereferences', [], []],
        ];
    }

    public function testSupportsFindByIdentifiers(): void
    {
        if (!class_exists(FrontendUser::class)) {
            $this->markTestSkipped('Skipping test with FrontendUser dependency');
        }
        $repository = $this->getMockBuilder(FrontendUserGroupRepository::class)
            ->setMethods(['findByUid'])
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(2))->method('findByUid')->willReturnArgument(0);

        $identifiers = ['foobar', 'foobar2'];

        $result = $this->callInaccessibleMethod(
            new FormDataTransformer(...$this->getConstructorArguments()),
            'loadObjectsFromRepository',
            $repository,
            $identifiers
        );
        $this->assertEquals($result, ['foobar', 'foobar2']);
    }
}
