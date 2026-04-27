<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Result;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class DoctrineQueryProxy
{
    public static function executeStatementOnQueryBuilder(QueryBuilder $queryBuilder): int
    {
        if (method_exists($queryBuilder, 'executeStatement')) {
            return $queryBuilder->executeStatement();
        }
        /** @var int $result */
        $result = $queryBuilder->execute();
        return $result;
    }

    public static function executeQueryOnQueryBuilder(QueryBuilder $queryBuilder): Result
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
            $output = $result->fetchAssociative();
        } else {
            /** @var array|null $output */
            $output = $result->fetch(FetchMode::ASSOCIATIVE);
        }

        return $output ?: null;
    }

    public static function fetchAllAssociative(Result $result): array
    {
        if (method_exists($result, 'fetchAllAssociative')) {
            return $result->fetchAllAssociative() ?: [];
        }
        return $result->fetchAll(FetchMode::ASSOCIATIVE) ?: [];
    }
}
