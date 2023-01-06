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
    /**
     * @param string $table
     * @param string $fields
     * @param string $clause
     * @param string $groupBy
     * @param string $orderBy
     * @param integer $limit
     * @param integer $offset
     * @return array|null
     */
    public function get($table, $fields, $clause = null, $groupBy = null, $orderBy = null, $limit = 0, $offset = 0)
    {
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

    /**
     * @param string $table
     * @param string $fields
     * @param integer $uid
     * @return array|null
     */
    public function getSingle($table, $fields, $uid)
    {
        if ($this->isBackendContext()) {
            return BackendUtility::getRecord($table, $uid, $fields);
        }
        $results = $this->getQueryBuilder($table)
            ->from($table)
            ->select(...explode(',', $fields))
            ->where(sprintf('uid = %d', $uid))
            ->execute()
            ->fetchAll() ?: [];
        $firstResult = reset($results);
        return $firstResult ? (array) $firstResult : null;
    }

    /**
     * @param string $table
     * @param array $record
     * @return boolean|Statement|ResultStatement|Result|int
     */
    public function update($table, array $record)
    {
        $builder = $this->getQueryBuilder($table)->update($table)->where(sprintf('uid = %d', $record['uid']));
        foreach ($record as $name => $value) {
            $builder->set($name, $value);
        }
        return $builder->execute();
    }

    /**
     * @param string $table
     * @param mixed $recordOrUid
     * @return boolean
     */
    public function delete($table, $recordOrUid)
    {
        $clauseUid = true === is_array($recordOrUid) ? $recordOrUid['uid'] : $recordOrUid;
        $clause = "uid = '" . intval($clauseUid) . "'";
        return (bool) $this->getQueryBuilder($table)->delete($table)->where($clause)->execute();
    }

    /**
     * @param string $table
     * @param string $fields
     * @param string $condition
     * @param array $values
     * @return array
     */
    public function preparedGet($table, $fields, $condition, $values = [])
    {
        return $this->getQueryBuilder($table)
            ->select(...explode(',', $fields))
            ->from($table)
            ->where($condition)
            ->setParameters($values)
            ->execute()
            ->fetchAll();
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    protected function getQueryBuilder($table)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $this->setContextDependentRestrictionsForQueryBuilder($queryBuilder);
        return $queryBuilder;
    }

    /**
     * @codeCoverageIgnore
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    protected function setContextDependentRestrictionsForQueryBuilder(QueryBuilder $queryBuilder)
    {
        if ($this->isBackendContext()) {
            $queryBuilder->getRestrictions()->removeAll();
        } else {
            if ((bool)($GLOBALS['TSFE']->fePreview ?? false)) {
                $context = new Context();
                $visibility = new VisibilityAspect(true, true);
                $context->setAspect('visibility', $visibility);
                /** @var FrontendRestrictionContainer $frontendRestrictions */
                $frontendRestrictions = GeneralUtility::makeInstance(FrontendRestrictionContainer::class, $context);
                $queryBuilder->getRestrictions()->removeAll()->add($frontendRestrictions);
            }
        }
    }

    protected function isBackendContext(): bool
    {
        if (defined('TYPO3_MODE')) {
            return TYPO3_MODE !== 'FE';
        }
        /** @var ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        return ApplicationType::fromRequest($request)->isFrontend();
    }
}
