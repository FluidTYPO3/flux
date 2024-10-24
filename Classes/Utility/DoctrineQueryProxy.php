<?php
namespace FluidTYPO3\Flux\Utility;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Result;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class DoctrineQueryProxy
{
    /**
     * Returns \Doctrine\DBAL\Result on v11+, \Doctrine\DBAL\Driver\ResultStatement on v10
     *
     * @return Result
     */
    public static function executeQueryOnQueryBuilder(QueryBuilder $queryBuilder)
    {
        if (method_exists($queryBuilder, 'executeQuery')) {
            return $queryBuilder->executeQuery();
        }
        /** @var Result $result */
        $result = $queryBuilder->execute();
        return $result;
    }

    public static function fetchAssociative(Result $result): ?array
    {
        if (method_exists($result, 'fetchAssociative')) {
            /** @var array|null $output */
            $output = $result->fetchAssociative() ?: null;
        } else {
            /** @var array|null $output */
            $output = $result->fetch(FetchMode::ASSOCIATIVE);
        }

        return $output;
    }

    public static function fetchAllAssociative(Result $result): array
    {
        if (method_exists($result, 'fetchAllAssociative')) {
            return $result->fetchAllAssociative() ?: [];
        }
        return $result->fetchAll(FetchMode::ASSOCIATIVE) ?: [];
    }
}
