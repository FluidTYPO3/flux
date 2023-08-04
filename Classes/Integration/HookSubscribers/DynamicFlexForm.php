<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\FlexFormBuilder;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DynamicFlexForm extends FlexFormTools
{
    protected FlexFormBuilder $flexFormBuilder;

    protected static bool $recursed = false;

    public function __construct()
    {
        /** @var FlexFormBuilder $flexFormBuilder */
        $flexFormBuilder = GeneralUtility::makeInstance(FlexFormBuilder::class);
        $this->flexFormBuilder = $flexFormBuilder;
    }

    /**
     * Method to generate a custom identifier for a Flux-based DS.
     * The custom identifier must include a record ID, which we
     * can then use to restore the record.
     */
    public function getDataStructureIdentifierPreProcess(
        array $tca,
        string $tableName,
        string $fieldName,
        array $record
    ): array {
        if (static::$recursed) {
            return [];
        }
        static::$recursed = true;
        /** @var string|array $originalIdentifier */
        $originalIdentifier = $this->getDataStructureIdentifier(
            [ 'config' => $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config']],
            $tableName,
            $fieldName,
            $record
        );
        static::$recursed = false;
        if (is_string($originalIdentifier)) {
            /** @var array $originalIdentifier */
            $originalIdentifier = json_decode($originalIdentifier, true);
        }
        return $this->flexFormBuilder->resolveDataStructureIdentifier(
            $tableName,
            $fieldName,
            $record,
            $originalIdentifier
        );
    }

    public function parseDataStructureByIdentifierPreProcess(array $identifier): array
    {
        return $this->flexFormBuilder->parseDataStructureByIdentifier($identifier);
    }
}
