<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Attribute\DataTransformer;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\OptionCarryingInterface;
use FluidTYPO3\Flux\Form\Transformation\DataTransformerInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;

/**
 * File Transformer
 */
#[DataTransformer('flux.datatransformer.file')]
class FileTransformer implements DataTransformerInterface
{
    private FileRepository $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function canTransformToType(string $type): bool
    {
        return in_array($type, ['file', 'files', 'filereference', 'filereferences'], true);
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @param string|array $value
     * @return File|FileReference|File[]|FileReference[]|null
     */
    public function transform(FormInterface $component, string $type, $value)
    {
        /** @var OptionCarryingInterface $form */
        $form = $component->getRoot();

        /** @var string $table */
        $table = $form->getOption(FormOption::RECORD_TABLE);
        /** @var array $record */
        $record = $form->getOption(FormOption::RECORD);

        $references = $this->fileRepository->findByRelation($table, (string) $component->getName(), $record['uid']);

        switch ($type) {
            case 'file':
                if (!empty($references)) {
                    return $references[0]->getOriginalFile();
                }
                return null;
            case 'files':
                $files = [];
                foreach ($references as $reference) {
                    $files[] = $reference->getOriginalFile();
                }
                return $files;
            case 'filereference':
                return $references[0] ?? null;
            case 'filereferences':
                return $references;
        }

        return null;
    }
}
