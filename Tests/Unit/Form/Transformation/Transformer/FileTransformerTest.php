<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\Transformer\FileTransformer;
use FluidTYPO3\Flux\Tests\Mock\QueryBuilder;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;

class FileTransformerTest extends AbstractTestCase
{
    private ConnectionPool $connectionPool;
    private ResourceFactory $resourceFactory;
    private FileTransformer $subject;

    protected function setUp(): void
    {
        $this->connectionPool = $this->getMockBuilder(ConnectionPool::class)
            ->onlyMethods(['getQueryBuilderForTable'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceFactory = $this->getMockBuilder(ResourceFactory::class)
            ->onlyMethods(['getFileReferenceObject'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = new FileTransformer($this->connectionPool, $this->resourceFactory);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'][ExtensionOption::OPTION_UNIQUE_FILE_FIELD_NAMES]);
        parent::tearDown();
    }

    public function testGetPriority(): void
    {
        self::assertSame(0, $this->subject->getPriority());
    }

    /**
     * @dataProvider getCanTransformToTypeTestValues
     */
    public function testCanTransformToType(bool $expected, string $type): void
    {
        self::assertSame($expected, $this->subject->canTransformToType($type));
    }

    public function getCanTransformToTypeTestValues(): array
    {
        return [
            'supports file' => [true, 'file'],
            'supports files' => [true, 'files'],
            'supports filereference' => [true, 'filereference'],
            'supports filereferences' => [true, 'filereferences'],
            'does not support integer' => [false, 'integer'],
            'does not support string' => [false, 'string'],
        ];
    }

    /**
     * @dataProvider getTransformWithFileTargetTypesTestValues
     * @param mixed $expected
     */
    public function testTransformationWithFileTargetTypes(
        string $type,
        array $files,
        bool $exception,
        bool $uniqueFilenamesExtensionSetting,
        $expected
    ): void {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'][ExtensionOption::OPTION_UNIQUE_FILE_FIELD_NAMES]
            = $uniqueFilenamesExtensionSetting;

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn(
            new QueryBuilder(array_fill(0, count($files), ['uid' => 1]))
        );
        if ($exception) {
            $this->resourceFactory->method('getFileReferenceObject')
                ->willThrowException(new ResourceDoesNotExistException());
        } else {
            $this->resourceFactory->method('getFileReferenceObject')->willReturnOnConsecutiveCalls(...$files);
        }

        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        $form->setOption(FormOption::RECORD_TABLE, 'tt_content');
        $form->setOption(FormOption::RECORD, ['uid' => 1]);

        $component = $form->createField(Form\Field\Input::class, 'field')->setTransform($type);
        $transformed = $this->subject->transform($component, $type, 1);

        self::assertSame($expected, $transformed);
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

        $set = [
            'file, non-empty' => ['file', [$fileReference1], false, false, $file1],
            'file, non-empty, but not found' => ['file', [$fileReference1], true, false, null],
            'files, non-empty' => ['files', [$fileReference1, $fileReference2], false, false, [$file1, $file2]],
            'filereference, non-empty' => ['filereference', [$fileReference1], false, false, $fileReference1],
            'filereferences, non-empty' => [
                'filereferences',
                [$fileReference1, $fileReference2],
                false,
                false,
                [$fileReference1, $fileReference2]
            ],
            'file, empty' => ['file', [], false, false, null],
            'files, empty' => ['files', [], false, false, []],
            'filereference, empty' => ['filereference', [], false, false, null],
            'filereferences, empty' => ['filereferences', [], false, false, []],
            'incompatible type' => ['incompatible', [], false, false, null],
        ];
        $copy = $set;

        foreach ($copy as $name => $values) {
            // Duplicate all test values but flip the "unique filenames" extension setting.
            $values[3] = true;
            $set[$name . ', unique field name'] = $values;
        }

        return $set;
    }
}
