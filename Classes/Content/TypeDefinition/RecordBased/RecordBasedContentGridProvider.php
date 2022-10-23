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
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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

    /**
     * @param ContentTypeManager $contentTypes
     * @return void
     */
    public function injectContentTypes(ContentTypeManager $contentTypes)
    {
        $this->contentTypeDefinitions = $contentTypes;
    }

    /**
     * @param array $row
     * @param string $table
     * @param string|null $field
     * @param string|null $extensionKey
     * @return bool
     */
    public function trigger(array $row, $table, $field, $extensionKey = null)
    {
        return $table === $this->tableName && $field === $this->fieldName;
    }

    /**
     * @param array $row
     * @param array $dataStructure
     * @param array $conf
     * @return void
     */
    public function postProcessDataStructure(array &$row, &$dataStructure, array $conf)
    {
        // Reset the dummy data structure which has no sheets.
        $dataStructure = [];
        parent::postProcessDataStructure($row, $dataStructure, $conf);
    }

    /**
     * @param array $row
     * @return ContentGridForm
     */
    public function getForm(array $row)
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var ContentGridForm $contentGridForm */
        $contentGridForm = $objectManager->get(ContentGridForm::class);
        return $contentGridForm;
    }

    /**
     * @param array $row
     * @return Grid
     */
    public function getGrid(array $row)
    {
        $contentTypeDefinition = $this->contentTypeDefinitions->determineContentTypeForRecord($row);
        if (!($contentTypeDefinition instanceof ContentTypeDefinitionInterface)) {
            return parent::getGrid($row);
        }
        return $contentTypeDefinition->getGrid() ?? parent::getGrid($row);
    }
}
