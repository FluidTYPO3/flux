<?php
namespace FluidTYPO3\Flux\Content\TypeDefinition\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentGridForm;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Flux provider class to handle Grid integration with content types
 * defined in database records through RecordBasedContentTypeDefinition.
 *
 * Renders the grid configuration contained in column "grid", and is
 * capable of returning the vanilla Flux content type form, but other
 * than that, does not provide any special functionality.
 */
class RecordBasedContentGridProvider extends AbstractProvider implements GridProviderInterface
{
    protected $tableName = 'content_types';

    protected $fieldName = 'grid';

    protected $extensionKey = 'FluidTYPO3.Builder';

    /**
     * @var ContentTypeManager
     */
    protected $contentTypeDefinitions;

    public function injectContentTypes(ContentTypeManager $contentTypes)
    {
        $this->contentTypeDefinitions = $contentTypes;
    }

    public function trigger(array $row, $table, $field, $extensionKey = null)
    {
        return $table === $this->tableName && $field === $this->fieldName;
    }

    public function postProcessDataStructure(array &$row, &$dataStructure, array $conf)
    {
        // Reset the dummy data structure which has no sheets.
        $dataStructure = [];
        parent::postProcessDataStructure($row, $dataStructure, $conf);
    }

    public function getForm(array $row)
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(ContentGridForm::class);
    }

    public function getGrid(array $row)
    {
        return $this->contentTypeDefinitions->determineContentTypeForRecord($row)->getGrid() ?? parent::getGrid($row);
    }
}
