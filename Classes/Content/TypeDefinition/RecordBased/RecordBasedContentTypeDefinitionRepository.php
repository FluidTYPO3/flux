<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\Exception\TableNotFoundException;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class RecordBasedContentTypeDefinitionRepository implements SingletonInterface
{
    private ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    /**
     * @return RecordBasedContentTypeDefinition[]|array
     */
    public function fetchContentTypeDefinitions(): array
    {
        $definitions = [];
        try {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('content_types');
            /** @var string[] $keys */
            $keys = array_keys($GLOBALS['TCA']['content_types']['columns'] ?? ['*' => '']);
            /** @var array[] $typeRecords */
            $typeRecords = $queryBuilder->select(...$keys)
                ->from('content_types')
                ->where(
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
                )
                ->orderBy('sorting', 'ASC')
                ->execute()
                ->fetchAll();
        } catch (TableNotFoundException $exception) {
            $typeRecords = [];
        }

        foreach ($typeRecords as $typeRecord) {
            $extensionIdentity = $typeRecord['extension_identity'];
            if (empty($extensionIdentity)
                || !ExtensionManagementUtility::isLoaded(
                    ExtensionNamingUtility::getExtensionKey($extensionIdentity)
                )
            ) {
                $typeRecord['extension_identity'] = 'FluidTYPO3.Builder';
            }

            $contentType = new RecordBasedContentTypeDefinition($typeRecord);
            $definitions[$typeRecord['content_type']] = $contentType;
        }
        return $definitions;
    }
}
