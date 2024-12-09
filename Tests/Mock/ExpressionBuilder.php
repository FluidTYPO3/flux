<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Mock;

use Doctrine\DBAL\Platforms\TrimMode;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;

class ExpressionBuilder extends \TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder
{
    public function __construct()
    {
    }

    public function andX(...$expressions): CompositeExpression
    {
        return __FUNCTION__;
    }

    public function orX(...$expressions): CompositeExpression
    {
        return __FUNCTION__;
    }

    public function and(...$expressions): CompositeExpression
    {
        return __FUNCTION__;
    }

    public function or(...$expressions): CompositeExpression
    {
        return __FUNCTION__;
    }

    public function comparison($leftExpression, string $operator, $rightExpression): string
    {
        return __FUNCTION__;
    }

    public function eq(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function neq(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function lt($fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function lte(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function gt(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function gte(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function isNull(string $fieldName): string
    {
        return __FUNCTION__;
    }

    public function isNotNull(string $fieldName): string
    {
        return __FUNCTION__;
    }

    public function like(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function notLike(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function in(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function notIn(string $fieldName, $value): string
    {
        return __FUNCTION__;
    }

    public function inSet(string $fieldName, string $value, bool $isColumn = false): string
    {
        return __FUNCTION__;
    }

    public function notInSet(string $fieldName, string $value, bool $isColumn = false): string
    {
        return __FUNCTION__;
    }

    public function bitAnd(string $fieldName, int $value): string
    {
        return __FUNCTION__;
    }

    public function min(string $fieldName, string $alias = null): string
    {
        return __FUNCTION__;
    }

    public function max(string $fieldName, string $alias = null): string
    {
        return __FUNCTION__;
    }

    public function avg(string $fieldName, string $alias = null): string
    {
        return __FUNCTION__;
    }

    public function sum(string $fieldName, string $alias = null): string
    {
        return __FUNCTION__;
    }

    public function count(string $fieldName, string $alias = null): string
    {
        return __FUNCTION__;
    }

    public function length(string $fieldName, string $alias = null): string
    {
        return __FUNCTION__;
    }

    protected function calculation(string $aggregateName, string $fieldName, string $alias = null): string
    {
        return __FUNCTION__;
    }

    public function trim(string $fieldName, int $position = TrimMode::UNSPECIFIED, string $char = null)
    {
        return __FUNCTION__;
    }

    public function literal($input, $type = Connection::PARAM_STR)
    {
        return __FUNCTION__;
    }

    protected function unquoteLiteral(string $value): string
    {
        return __FUNCTION__;
    }
}
