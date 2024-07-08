<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Mock;

use TYPO3\CMS\Core\Database\Connection;

class QueryBuilder extends \TYPO3\CMS\Core\Database\Query\QueryBuilder
{
    private Result $result;
    private ExpressionBuilder $expressionBuilder;

    public function __construct(array ...$returns)
    {
        $this->result = new Result(...$returns);
        $this->expressionBuilder = new ExpressionBuilder();
    }

    public function execute(): Result
    {
        return $this->result;
    }

    public function expr(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }

    public function createNamedParameter($value, int $type = Connection::PARAM_STR, string $placeHolder = null): string
    {
        return 'p';
    }

    public function setMaxResults(int $maxResults): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function select(string ...$selects): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function addSelect(string ...$selects): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function selectLiteral(string ...$selects): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function addSelectLiteral(string ...$selects): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function from(string $from, string $alias = null): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function join(
        string $fromAlias,
        string $join,
        string $alias,
        string $condition = null
    ): \TYPO3\CMS\Core\Database\Query\QueryBuilder {
        return $this;
    }

    public function where(...$predicates): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function andWhere(...$where): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function orWhere(...$where): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function groupBy(...$groupBy): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function orderBy(string $fieldName, string $order = null): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }

    public function addOrderBy(string $fieldName, string $order = null): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return $this;
    }
}
