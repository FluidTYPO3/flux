<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\Transformer\FileTransformer;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;

class FileTransformerTest extends AbstractTestCase
{
    private FileRepository $fileRepository;

    protected function setUp(): void
    {
        $this->fileRepository = $this->getMockBuilder(FileRepository::class)
            ->onlyMethods(['findByRelation'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = new FileTransformer($this->fileRepository);

        parent::setUp();
    }

    /**
     * @dataProvider getTransformWithFileTargetTypesTestValues
     * @param mixed $expected
     */
    public function testTransformationWithFileTargetTypes(string $type, array $files, $expected): void
    {
        $this->fileRepository->method('findByRelation')->willReturn($files);

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

        return [
            'file, non-empty' => ['file', [$fileReference1], $file1],
            'files, non-empty' => ['files', [$fileReference1, $fileReference2], [$file1, $file2]],
            'filereference, non-empty' => ['filereference', [$fileReference1], $fileReference1],
            'filereferences, non-empty' => [
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
}
