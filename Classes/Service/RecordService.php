<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Result;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to wrap around record operations normally going through
 * the $TYPO3_DB global variable.
 */
class RecordService implements SingletonInterface
{
    public function get(
        string $table,
        string $fields,
        ?string $clause = null,
        ?string $groupBy = null,
        ?string $orderBy = null,
        int $limit = 0,
        int $offset = 0
    ): ?array {
        $statement = $this->getQueryBuilder($table)->from($table)->select(...explode(',', $fields));

        if ($groupBy) {
            $statement->groupBy($groupBy);
        }
        if ($orderBy) {
            $statement->orderBy(...explode(' ', $orderBy));
        }
        if ($clause) {
            $statement->where($clause);
        }
        if ($limit) {
            $statement->setMaxResults($limit);
        }
        if ($offset) {
            $statement->setFirstResult($offset);
        }

        return $statement->execute()->fetchAll();
    }

    public function getSingle(string $table, string $fields, int $uid): ?array
    {
        if ($this->isBackendOrPreviewContext()) {
            return BackendUtility::getRecord($table, $uid, $fields);
        }
        $queryBuilder = $this->getQueryBuilder($table);
        $results = $queryBuilder->from($table)
            ->select(...explode(',', $fields))
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid)))
            ->execute()
            ->fetchAll() ?: [];
        $firstResult = reset($results);
        return $firstResult ? (array) $firstResult : null;
    }

    /**
     * @return boolean|Statement|ResultStatement|Result|int
     */
    public function update(string $table, array $record)
    {
        $queryBuilder = $this->getQueryBuilder($table);
        $builder = $queryBuilder->update($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($record['uid'])));
        foreach ($record as $name => $value) {
            $builder->set($name, $value);
        }
        return $builder->execute();
    }

    /**
     * @param int|array $recordOrUid
     */
    public function delete(string $table, $recordOrUid): bool
    {
        $clauseUid = true === is_array($recordOrUid) ? $recordOrUid['uid'] : $recordOrUid;
        $queryBuilder = $this->getQueryBuilder($table);
        return (bool) $queryBuilder->delete($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($clauseUid)))
            ->execute();
    }

    public function preparedGet(string $table, string $fields, string $condition, array $values = []): array
    {
        return $this->getQueryBuilder($table)
            ->select(...explode(',', $fields))
            ->from($table)
            ->where($condition)
            ->setParameters($values)
            ->execute()
            ->fetchAll();
    }

    protected function getQueryBuilder(string $table): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $this->setContextDependentRestrictionsForQueryBuilder($queryBuilder);
        return $queryBuilder;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function setContextDependentRestrictionsForQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if (!$this->isBackendOrPreviewContext()) {
            return;
        }

        if ($this->isPreviewContext()) {
            $context = new Context();
            $visibility = new VisibilityAspect(true, true);
            $context->setAspect('visibility', $visibility);
            /** @var FrontendRestrictionContainer $frontendRestrictions */
            $frontendRestrictions = GeneralUtility::makeInstance(FrontendRestrictionContainer::class, $context);
            $queryBuilder->getRestrictions()->removeAll()->add($frontendRestrictions);
        } else {
            $queryBuilder->getRestrictions()->removeAll();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function isBackendOrPreviewContext(): bool
    {
        /** @var ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        return !ApplicationType::fromRequest($request)->isFrontend() || $this->isPreviewContext();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function isPreviewContext(): bool
    {
        /** @var ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        if (ApplicationType::fromRequest($request)->isFrontend()) {
            /** @var Context $context */
            $context = GeneralUtility::makeInstance(Context::class);
            return $context->hasAspect('frontend.preview')
                && $context->getPropertyFromAspect('frontend.preview', 'isPreview');
        }
        return false;
    }
}
