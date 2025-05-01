<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Mock;

// @codingStandardsIgnoreStart
if (class_exists(\Doctrine\DBAL\ForwardCompatibility\Result::class)) {
    if (version_compare(PHP_VERSION, '8', '>=')) {
        class Result extends \Doctrine\DBAL\ForwardCompatibility\Result
        {
            use ResultTraitUnion;
        }
    } else {
        class Result extends \Doctrine\DBAL\ForwardCompatibility\Result
        {
            use ResultTrait;
        }
    }
} else {
    if (version_compare(PHP_VERSION, '8', '>=')) {
        class Result extends \Doctrine\DBAL\Result
        {
            use ResultTraitUnion;
        }
    } else {
        class Result extends \Doctrine\DBAL\Result
        {
            use ResultTrait;
        }
    }
}
