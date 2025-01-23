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
use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\OptionCarryingInterface;
use FluidTYPO3\Flux\Form\Transformation\DataTransformerInterface;
use FluidTYPO3\Flux\Proxy\ResourceFactoryProxy;
use FluidTYPO3\Flux\Utility\DoctrineQueryProxy;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * File Transformer
 */
#[DataTransformer('flux.datatransformer.file')]
class FileTransformer implements DataTransformerInterface
{
    private ConnectionPool $connectionPool;
    private ResourceFactoryProxy $resourceFactory;

    public function __construct(ConnectionPool $connectionPool, ResourceFactoryProxy $resourceFactory)
    {
        $this->connectionPool = $connectionPool;
        $this->resourceFactory = $resourceFactory;
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

        $fieldName = (string) $component->getName();
        if (ExtensionConfigurationUtility::getOption(ExtensionOption::OPTION_UNIQUE_FILE_FIELD_NAMES)) {
            $fieldName = $form->getOption(FormOption::RECORD_FIELD) . '.' . $fieldName;
        }

        $references = $this->fetchFileReferences($table, $fieldName, (integer) $record['uid']);

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

    protected function fetchFileReferences(string $table, string $fieldName, int $recordUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            ->select('uid')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($recordUid)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($table)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($fieldName))
            )
            ->orderBy('sorting_foreign');

        $result = DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder);

        $references = [];
        while ($row = DoctrineQueryProxy::fetchAssociative($result)) {
            /** @var array<string, int> $row */
            try {
                $references[] = $this->resourceFactory->getFileReferenceObject($row['uid']);
            } catch (ResourceDoesNotExistException $exception) {
                // Not handled - defunct references are just ignored.
            }
        }

        return $references;
    }
}
