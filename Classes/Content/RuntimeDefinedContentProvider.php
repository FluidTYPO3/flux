<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;

/**
 * Flux Provider for runtime-defined content types
 *
 * Provides Flux integration for content types that are defined
 * during runtime, for example record-based content types.
 *
 * Is essentially a proxy class which returns values and objects
 * from a ContentTypeDefinitionInterface implementation, masquerading
 * it as a Flux Provider.
 */
class RuntimeDefinedContentProvider extends AbstractProvider implements GridProviderInterface
{
    protected $tableName = 'tt_content';
    protected $fieldName = 'pi_flexform';
    protected $extensionKey = 'FluidTYPO3.Flux';
    protected $priority = 90;

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
        if ($table !== $this->tableName || $field !== $this->fieldName || $field === null) {
            return false;
        }
        $contentTypeDefinition = $this->contentTypeDefinitions->determineContentTypeForRecord($row);
        if (!$contentTypeDefinition) {
            return false;
        }
        $contentTypeName = $contentTypeDefinition->getContentTypeName();
        $registeredContentTypes = RecordBasedContentTypeDefinition::fetchContentTypes();
        return $contentTypeName && isset($registeredContentTypes[$contentTypeName]);
    }

    public function getControllerExtensionKeyFromRecord(array $row)
    {
        return ExtensionNamingUtility::getExtensionKey($this->getExtensionKey($row));
    }

    public function getControllerActionFromRecord(array $row)
    {
        return 'proxy';
    }

    public function getExtensionKey(array $row)
    {
        return $this->getContentTypeDefinition($row)->getExtensionIdentity();
    }

    public function postProcessDataStructure(array &$row, &$dataStructure, array $conf)
    {
        // Reset the dummy data structure which has no sheets.
        $dataStructure = [];
        parent::postProcessDataStructure($row, $dataStructure, $conf);
    }

    public function getGrid(array $row)
    {
        return $this->getContentTypeDefinition($row)->getGrid() ?? parent::getGrid($row);
    }

    public function getForm(array $row)
    {
        return $this->getContentTypeDefinition($row)->getForm();
    }

    public function getTemplatePathAndFilename(array $row)
    {
        return $this->getContentTypeDefinition($row)->getTemplatePathAndFilename();
    }

    public function getTemplateVariables(array $row)
    {
        $variables = parent::getTemplateVariables($row);
        $variables['contentType'] = $this->contentTypeDefinitions->determineContentTypeForRecord($row);
        $variables['provider'] = $this;
        return $variables;
    }

    protected function getContentTypeDefinition(array $row): FluidRenderingContentTypeDefinitionInterface
    {
        $definition = $this->contentTypeDefinitions->determineContentTypeForRecord($row);
        if (!$definition instanceof FluidRenderingContentTypeDefinitionInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Content type definition for %s must implement interface %s, class %s does not.',
                    $row['CType'],
                    FluidRenderingContentTypeDefinitionInterface::class,
                    get_class($definition)
                ),
                1556109085
            );
        }
        return $definition;
    }
}
