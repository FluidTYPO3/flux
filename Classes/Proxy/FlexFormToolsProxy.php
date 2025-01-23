<?php
namespace FluidTYPO3\Flux\Proxy;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;

/**
 * The "readonly" keyword is poison to FOSS libraries. And no, it is not necessary. Not even remotely.
 *
 * @codeCoverageIgnore
 */
class FlexFormToolsProxy
{
    private FlexFormTools $flexFormTools;

    public function __construct(FlexFormTools $flexFormTools)
    {
        $this->flexFormTools = $flexFormTools;
    }

    public function getDataStructureIdentifier(
        array $fieldTca,
        string $tableName,
        string $fieldName,
        array $row
    ): string {
        return $this->flexFormTools->getDataStructureIdentifier($fieldTca, $tableName, $fieldName, $row);
    }

    public function parseDataStructureByIdentifier(string $identifier): array
    {
        return $this->flexFormTools->parseDataStructureByIdentifier($identifier);
    }
}
