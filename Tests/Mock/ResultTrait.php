<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Mock;

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

    public function rowCount(): int
    {
        return count($this->returns);
    }
}
