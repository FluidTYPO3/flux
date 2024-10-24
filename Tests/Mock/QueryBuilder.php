<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Mock;

use FluidTYPO3\Flux\Tests\Mock\Result;
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

    public function count(string $item): self
    {
        return $this;
    }

    public function delete(string $delete, string $alias = null): self
    {
        return $this;
    }

    public function update(string $update, string $alias = null): self
    {
        return $this;
    }

    public function insert(string $insert): self
    {
        return $this;
    }

    public function set(string $key, $value, bool $createNamedParameter = true, int $type = \PDO::PARAM_STR): self
    {
        return $this;
    }


    public function execute(): Result
    {
        return $this->result;
    }

    public function executeQuery(): Result
    {
        return $this->result;
    }

    public function executeStatement(): int
    {
        return $this->result->rowCount();
    }

    public function expr(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }

    public function createNamedParameter($value, int $type = Connection::PARAM_STR, string $placeHolder = null): string
    {
        return 'p';
    }

    public function setMaxResults(int $maxResults): self
    {
        return $this;
    }

    public function select(string ...$selects): self
    {
        return $this;
    }

    public function addSelect(string ...$selects): self
    {
        return $this;
    }

    public function selectLiteral(string ...$selects): self
    {
        return $this;
    }

    public function addSelectLiteral(string ...$selects): self
    {
        return $this;
    }

    public function from(string $from, string $alias = null): self
    {
        return $this;
    }

    public function setParameter($key, $value, int $type = null): self
    {
        return $this;
    }

    public function setParameters(array $params, array $types = []): self
    {
        return $this;
    }

    public function setFirstResult(int $firstResult): self
    {
        return $this;
    }

    public function join(string $fromAlias, string $join, string $alias, string $condition = null): self
    {
        return $this;
    }

    public function where(...$predicates): self
    {
        return $this;
    }

    public function andWhere(...$where): self
    {
        return $this;
    }

    public function orWhere(...$where): self
    {
        return $this;
    }

    public function groupBy(...$groupBy): self
    {
        return $this;
    }

    public function orderBy(string $fieldName, string $order = null): self
    {
        return $this;
    }

    public function addOrderBy(string $fieldName, string $order = null): self
    {
        return $this;
    }

    public function getQueryPart(string $queryPartName)
    {
        return '';
    }

    public function getQueryParts(): array
    {
        return [];
    }

    protected function addAdditionalWhereConditions()
    {
    }
}
