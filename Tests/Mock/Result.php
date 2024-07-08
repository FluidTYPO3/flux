<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Mock;

// @codingStandardsIgnoreStart
if (class_exists(\Doctrine\DBAL\ForwardCompatibility\Result::class)) {
    class Result extends \Doctrine\DBAL\ForwardCompatibility\Result
    {
        use ResultTrait;
    }
} else {
    class Result extends \Doctrine\DBAL\Result
    {
        use ResultTrait;
    }
}

trait ResultTrait
{
    private array $returns;

    public function __construct(array $returns)
    {
        $this->returns = $returns;
    }

    public function fetchAssociative()
    {
        return array_shift($this->returns);
    }

    public function fetchOne()
    {
        return array_shift($this->returns);
    }

    public function fetchAllAssociative(): array
    {
        return $this->returns;
    }
}
