<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Content\ContentGridForm;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    protected ?string $tableName = 'content_types';
    protected ?string $fieldName = 'grid';
    protected string $extensionKey = 'FluidTYPO3.Flux';

    protected ContentTypeManager $contentTypeDefinitions;

    public function __construct(
        FluxService $configurationService,
        WorkspacesAwareRecordService $recordService,
        ViewBuilder $viewBuilder,
        CacheService $cacheService,
        TypoScriptService $typoScriptService,
        ContentTypeManager $contentTypeManager
    ) {
        parent::__construct($configurationService, $recordService, $viewBuilder, $cacheService, $typoScriptService);
        $this->contentTypeDefinitions = $contentTypeManager;
    }

    public function trigger(array $row, ?string $table, ?string $field, ?string $extensionKey = null): bool
    {
        return $table === $this->tableName && $field === $this->fieldName;
    }

    public function postProcessDataStructure(array &$row, ?array &$dataStructure, array $conf): void
    {
        // Reset the dummy data structure which has no sheets.
        $dataStructure = [];
        parent::postProcessDataStructure($row, $dataStructure, $conf);
    }

    public function getForm(array $row, ?string $forField = null): Form
    {
        /** @var ContentGridForm $contentGridForm */
        $contentGridForm = GeneralUtility::makeInstance(ContentGridForm::class);
        return $contentGridForm;
    }

    public function getGrid(array $row): Grid
    {
        $contentTypeDefinition = $this->contentTypeDefinitions->determineContentTypeForRecord($row);
        if (!($contentTypeDefinition instanceof ContentTypeDefinitionInterface)) {
            return parent::getGrid($row);
        }
        return $contentTypeDefinition->getGrid() ?? parent::getGrid($row);
    }
}
