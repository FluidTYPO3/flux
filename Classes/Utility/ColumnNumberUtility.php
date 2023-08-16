<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

/**
 * Column Number Calculation Utility
 *
 * Contains methods used to calculate column position
 * (colPos) numbers and reverse calculate parent UID
 * and local column numbers based on virtual ones.
 *
 * Thesarus:
 *
 * "Virtual column number" is the combined column number
 * consisting of the parent UID multiplied by the multiplier,
 * plus the local column number which is an int from 0-99.
 *
 * "Local column number" is the integer you put into the
 * Flux grid columns, from 0-99, to identify each column.
 * This value, plus parent UID multiplied by multiplier,
 * make up the virtual column number.
 *
 * "Parent UID" is of course just the UID of the parent
 * record which contains the grid that has columns.
 *
 * Note that on the page level, column position numbers
 * should not be calculated with this utility but should
 * instead be used "raw" so there is no virtual number
 * but only the usual colPos values (but limited to use
 * numbers from 0-99 to avoid collisions).
 */
abstract class ColumnNumberUtility
{
    const MULTIPLIER = 100;

    public static function calculateLocalColumnNumber(int $colPos): int
    {
        return (int) $colPos % static::MULTIPLIER;
    }

    public static function calculateParentUid(int $colPos): int
    {
        return (int) floor($colPos / static::MULTIPLIER);
    }

    public static function calculateColumnNumberForParentAndColumn(int $parentUid, int $columnNumber): int
    {
        return ($parentUid * static::MULTIPLIER) + $columnNumber;
    }

    public static function calculateParentUidAndColumnFromVirtualColumnNumber(int $virtualColumnNumber): array
    {
        return [
            static::calculateParentUid($virtualColumnNumber),
            static::calculateLocalColumnNumber($virtualColumnNumber)
        ];
    }

    /**
     * @return int[]
     */
    public static function calculateMinimumAndMaximumColumnNumberWithinParent(int $parentUid): array
    {
        return [
            $parentUid * static::MULTIPLIER,
            ($parentUid * static::MULTIPLIER) + (static::MULTIPLIER - 1)
        ];
    }
}
