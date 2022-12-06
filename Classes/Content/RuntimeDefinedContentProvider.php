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
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    protected ?string $tableName = 'tt_content';
    protected ?string $fieldName = 'pi_flexform';
    protected string $extensionKey = 'FluidTYPO3.Flux';
    protected int $priority = 90;

    protected ContentTypeManager $contentTypeDefinitions;

    public function __construct()
    {
        parent::__construct();

        /** @var ContentTypeManager $contentTypes */
        $contentTypes = GeneralUtility::makeInstance(ContentTypeManager::class);
        $this->contentTypeDefinitions = $contentTypes;
    }

    public function trigger(array $row, ?string $table, ?string $field, ?string $extensionKey = null): bool
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

    public function getControllerExtensionKeyFromRecord(array $row): string
    {
        return ExtensionNamingUtility::getExtensionKey((string) $this->getExtensionKey($row));
    }

    public function getControllerActionFromRecord(array $row): string
    {
        return 'proxy';
    }

    public function getExtensionKey(array $row): string
    {
        return $this->getContentTypeDefinition($row)->getExtensionIdentity();
    }

    public function postProcessDataStructure(array &$row, ?array &$dataStructure, array $conf): void
    {
        // Reset the dummy data structure which has no sheets.
        $dataStructure = [];
        parent::postProcessDataStructure($row, $dataStructure, $conf);
    }

    public function getGrid(array $row): Grid
    {
        return $this->getContentTypeDefinition($row)->getGrid() ?? parent::getGrid($row);
    }

    public function getForm(array $row): Form
    {
        return $this->getContentTypeDefinition($row)->getForm();
    }

    public function getTemplatePathAndFilename(array $row): string
    {
        return $this->getContentTypeDefinition($row)->getTemplatePathAndFilename();
    }

    public function getTemplateVariables(array $row): array
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
                    $definition !== null ? get_class($definition) : '(unknown)'
                ),
                1556109085
            );
        }
        return $definition;
    }
}
