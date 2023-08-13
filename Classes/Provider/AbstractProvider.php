<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\ViewInterface;

class AbstractProvider implements ProviderInterface
{
    const FORM_CLASS_PATTERN = '%s\\Form\\%s\\%sForm';
    const CONTENT_OBJECT_TYPE_LIST = 'list';

    /**
     * Fill with the table column name which should trigger this Provider.
     */
    protected ?string $fieldName = null;

    /**
     * Fill with the name of the DB table which should trigger this Provider.
     */
    protected ?string $tableName = null;

    /**
     * Fill with the "list_type" value that should trigger this Provider.
     */
    protected string $listType = '';

    /**
     * Fill with the "CType" value that should trigger this Provider.
     */
    protected string $contentObjectType = '';

    protected string $name = self::class;
    protected ?string $parentFieldName = null;
    protected ?array $row = null;
    protected ?string $templatePathAndFilename = null;
    protected array $templateVariables = [];
    protected ?array $templatePaths = null;
    protected ?string $configurationSectionName = 'Configuration';
    protected string $extensionKey = 'FluidTYPO3.Flux';
    protected ?string $pluginName = null;
    protected ?string $controllerName = null;
    protected string $controllerAction = 'default';
    protected int $priority = 50;
    protected ?Form $form = null;
    protected ?Grid $grid = null;

    protected FormDataTransformer $formDataTransformer;
    protected WorkspacesAwareRecordService $recordService;
    protected ViewBuilder $viewBuilder;
    protected CacheService $cacheService;
    protected TypoScriptService $typoScriptService;

    public function __construct(
        FormDataTransformer $formDataTransformer,
        WorkspacesAwareRecordService $recordService,
        ViewBuilder $viewBuilder,
        CacheService $cacheService,
        TypoScriptService $typoScriptService
    ) {
        $this->formDataTransformer = $formDataTransformer;
        $this->recordService = $recordService;
        $this->viewBuilder = $viewBuilder;
        $this->cacheService = $cacheService;
        $this->typoScriptService = $typoScriptService;
    }

    public function loadSettings(array $settings): void
    {
        if (isset($settings['name'])) {
            $this->setName($settings['name']);
        }
        if (isset($settings['form'])) {
            $form = Form::create($settings['form']);
            if (isset($settings['extensionKey'])) {
                $extensionKey = $settings['extensionKey'];
                $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
                $form->setExtensionName($extensionName);
            }
            $settings['form'] = $form;
        }
        if (isset($settings['grid'])) {
            $settings['grid'] = Grid::create($settings['grid']);
        }
        foreach ($settings as $name => $value) {
            $this->$name = $value;
        }
        $fieldName = $this->getFieldName([]);
        if (true === isset($settings['listType'])) {
            $listType = $settings['listType'];
            $GLOBALS['TCA'][$this->tableName]['types']['list']['subtypes_addlist'][$listType] = $fieldName;
        }
        $GLOBALS['TCA'][$this->tableName]['columns'][$fieldName]['config']['type'] = 'flex';
    }

    public function trigger(array $row, ?string $table, ?string $field, ?string $extensionKey = null): bool
    {
        $providerFieldName = $this->getFieldName($row);
        $providerTableName = $this->getTableName($row);
        $providerExtensionKey = $this->extensionKey;
        $contentObjectType = $this->contentObjectType;
        $listType = $this->listType;

        // Content type resolving: CType *may* be an array when called from certain FormEngine contexts, such as
        // user functions registered via userFunc.
        $contentTypeFromRecord = (is_array($row['CType'] ?? null) ? $row['CType'][0] : null) ?? $row['CType'] ?? null;
        $pluginTypeFromRecord = $row['list_type'] ?? null;

        $rowContainsPlugin = $contentTypeFromRecord === static::CONTENT_OBJECT_TYPE_LIST;
        $isContentRecord = $table === 'tt_content';
        $rowIsEmpty = (0 === count($row));
        $matchesContentType = $contentTypeFromRecord === $contentObjectType;
        $matchesPluginType = $rowContainsPlugin && $pluginTypeFromRecord === $listType;
        $matchesTableName = ($providerTableName === $table || !$table);
        $matchesFieldName = ($providerFieldName === $field || !$field);
        $matchesExtensionKey = ($providerExtensionKey === $extensionKey || !$extensionKey);

        // Requirements: must always match ext-key, table and field. If record is a content record, must additionally
        // match either Ctype and list_type, or must match CType in record that does not have a list_type.
        $isFullMatch = $matchesExtensionKey && $matchesTableName && $matchesFieldName
            && (!$isContentRecord || ($matchesContentType && ((!$rowContainsPlugin) || $matchesPluginType)));
        $isFallbackMatch = ($matchesTableName && $matchesFieldName && $rowIsEmpty);
        return ($isFullMatch || $isFallbackMatch);
    }

    /**
     * If not-NULL is returned, the value is used as
     * object class name when creating a Form implementation
     * instance which can be returned as form instead of
     * reading from template or overriding the getForm() method.
     *
     * @return class-string|null
     */
    protected function resolveFormClassName(array $row, ?string $forField = null): ?string
    {
        $packageName = $this->getControllerPackageNameFromRecord($row, $forField);
        $packageKey = str_replace('.', '\\', $packageName);
        $controllerName = $this->getControllerNameFromRecord($row);
        $action = $this->getControllerActionFromRecord($row, $forField);
        $expectedClassName = sprintf(static::FORM_CLASS_PATTERN, $packageKey, $controllerName, ucfirst($action));
        return class_exists($expectedClassName) ? $expectedClassName : null;
    }

    protected function getViewVariables(array $row, ?string $forField = null): array
    {
        $extensionKey = (string) $this->getExtensionKey($row, $forField);
        $fieldName = $forField ?? $this->getFieldName($row);
        $variables = [
            'record' => $row,
            'settings' => $this->typoScriptService->getSettingsForExtensionName($extensionKey)
        ];

        // Special case: when saving a new record variable $row[$fieldName] is already an array
        // and must not be processed by the configuration service. This has limited support from
        // Flux (essentially: no Form instance which means no inheritance, transformation or
        // form options can be dependended upon at this stage).
        if (isset($row[$fieldName]) && !is_array($row[$fieldName])) {
            $recordVariables = $this->formDataTransformer->convertFlexFormContentToArray($row[$fieldName]);
            $variables = RecursiveArrayUtility::mergeRecursiveOverrule($variables, $recordVariables);
        }

        $variables = RecursiveArrayUtility::mergeRecursiveOverrule($this->templateVariables, $variables);

        return $variables;
    }

    public function getForm(array $row, ?string $forField = null): ?Form
    {
        /** @var Form $form */
        $form = $this->form
            ?? $this->createCustomFormInstance($row, $forField)
            ?? $this->extractConfiguration($row, 'form', $forField)
            ?? Form::create();
        $form->setOption(Form::OPTION_RECORD, $row);
        $form->setOption(Form::OPTION_RECORD_TABLE, $this->getTableName($row));
        $form->setOption(Form::OPTION_RECORD_FIELD, $forField ?? $this->getFieldName($row));
        return $form;
    }

    protected function createCustomFormInstance(array $row, ?string $forField = null): ?Form
    {
        $formClassName = $this->resolveFormClassName($row, $forField);
        if ($formClassName !== null && class_exists($formClassName)) {
            $tableName = $this->getTableName($row);
            $fieldName = $forField ?? $this->getFieldName($row);
            $id = 'row_' . $row['uid'];
            if ($tableName) {
                $id = $tableName;
            }
            if ($fieldName) {
                $id .= '_' . $fieldName;
            }
            return $formClassName::create(['id' => $id]);
        }
        return null;
    }

    public function getGrid(array $row): Grid
    {
        if ($this->grid instanceof Grid) {
            return $this->grid;
        }
        $form = $this->getForm($row);
        if ($form) {
            $container = $this->detectContentContainerParent($form);
            if ($container) {
                $values = $this->getFlexFormValues($row);
                $contentContainer = $container->getContentContainer();
                $persistedObjects = [];
                if ($contentContainer instanceof Form\Container\SectionObject) {
                    $persistedObjects = array_column(
                        (array) (ObjectAccess::getProperty($values, (string) $container->getName()) ?? []),
                        (string) $contentContainer->getName()
                    );
                }

                // Determine the mode to render, then create an ad-hoc grid.
                /** @var Grid $grid */
                $grid = Grid::create();
                if ($container->getGridMode() === Form\Container\Section::GRID_MODE_ROWS) {
                    foreach ($persistedObjects as $index => $object) {
                        $gridRow = $grid->createContainer(Form\Container\Row::class, 'row' . $index);
                        $gridColumn = $gridRow->createContainer(
                            Form\Container\Column::class,
                            'column' . $object['colPos'],
                            $object['label'] ?? 'Column ' . $object['colPos']
                        );
                        $gridColumn->setColumnPosition((int) $object['colPos']);
                    }
                } elseif ($container->getGridMode() === Form\Container\Section::GRID_MODE_COLUMNS) {
                    $gridRow = $grid->createContainer(Form\Container\Row::class, 'row');
                    foreach ($persistedObjects as $index => $object) {
                        $gridColumn = $gridRow->createContainer(
                            Form\Container\Column::class,
                            'column' . $object['colPos'],
                            $object['label'] ?? 'Column ' . $object['colPos']
                        );
                        $gridColumn->setColumnPosition((int) $object['colPos']);
                        $gridColumn->setColSpan((int) ($object['colspan'] ?? 1));
                    }
                }
                return $grid;
            }
        }
        /** @var array $grids */
        $grids = $this->extractConfiguration($row, 'grids');
        $grid = $grids['grid'] ?? Grid::create();
        $grid->setExtensionName($grid->getExtensionName() ?: $this->getControllerExtensionKeyFromRecord($row));
        return $grid;
    }

    protected function detectContentContainerParent(Form\ContainerInterface $container): ?Form\Container\Section
    {
        if ($container instanceof Form\Container\SectionObject && $container->isContentContainer()) {
            /** @var Form\Container\Section $parent */
            $parent = $container->getParent();
            return $parent;
        }
        foreach ($container->getChildren() as $child) {
            if ($child instanceof Form\ContainerInterface
                && ($detected = $this->detectContentContainerParent($child))
            ) {
                return $detected;
            }
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    protected function extractConfiguration(array $row, ?string $name = null, ?string $forField = null)
    {
        $cacheKeyAll = $this->getCacheKeyForStoredVariable($row, '_all', $forField) . '_' . $forField;
        /** @var array $allCached */
        $allCached = $this->cacheService->getFromCaches($cacheKeyAll);
        $fromCache = $allCached[$name] ?? null;
        if ($fromCache) {
            return $fromCache;
        }
        $configurationSectionName = $this->getConfigurationSectionName($row, $forField);
        $viewVariables = $this->getViewVariables($row, $forField);
        $view = $this->getViewForRecord($row, $forField);
        $view->getRenderingContext()->getViewHelperVariableContainer()->addOrUpdate(
            FormViewHelper::class,
            FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME,
            $this->getExtensionKey($row, $forField)
        );

        try {
            if ($configurationSectionName) {
                $view->renderSection($configurationSectionName, $viewVariables, false);
            } else {
                $view->assignMultiple($viewVariables);
                $view->render();
            }
        } catch (InvalidTemplateResourceException $exception) {
            $this->dispatchFlashMessageForException($exception);
            return null;
        }

        $variables = $view->getRenderingContext()->getViewHelperVariableContainer()->getAll(FormViewHelper::class, []);
        if (isset($variables['form'])) {
            $variables['form']->setOption(
                Form::OPTION_TEMPLATEFILE,
                $this->getTemplatePathAndFilename($row, $forField)
            );
            if ($variables['form']->getOption(Form::OPTION_STATIC)) {
                $this->cacheService->setInCaches($variables, true, $cacheKeyAll);
            }
        }

        $returnValue = $name ? ($variables[$name] ?? null) : $variables;

        return HookHandler::trigger(
            HookHandler::PROVIDER_EXTRACTED_OBJECT,
            [
                'record' => $row,
                'name' => $name,
                'value' => $returnValue
            ]
        )['value'];
    }

    public function setListType(string $listType): self
    {
        $this->listType = $listType;
        return $this;
    }

    public function getListType(): string
    {
        return $this->listType;
    }

    public function setContentObjectType(string $contentObjectType): self
    {
        $this->contentObjectType = $contentObjectType;
        return $this;
    }

    public function getContentObjectType(): string
    {
        return $this->contentObjectType;
    }

    public function getFieldName(array $row): ?string
    {
        return $this->fieldName;
    }

    public function getParentFieldName(array $row): ?string
    {
        unset($row);
        return $this->parentFieldName;
    }

    public function getTableName(array $row): ?string
    {
        unset($row);
        return $this->tableName;
    }

    public function getTemplatePathAndFilename(array $row, ?string $forField = null): ?string
    {
        $templatePathAndFilename = (string) $this->templatePathAndFilename;
        if ($templatePathAndFilename !== '' && !PathUtility::isAbsolutePath($templatePathAndFilename)) {
            $templatePathAndFilename = $this->resolveAbsolutePathToFile($templatePathAndFilename);
        }
        if (true === empty($templatePathAndFilename)) {
            $templatePathAndFilename = null;
        }
        return HookHandler::trigger(
            HookHandler::PROVIDER_RESOLVED_TEMPLATE,
            [
                'template' => $templatePathAndFilename,
                'provider' => $this,
                'record' => $row
            ]
        )['template'];
    }

    /**
     * Converts the contents of the provided row's Flux-enabled field,
     * at the same time running through the inheritance tree generated
     * by getInheritanceTree() in order to apply inherited values.
     */
    public function getFlexFormValues(array $row, ?string $forField = null): array
    {
        $fieldName = $forField ?? $this->getFieldName($row);
        $form = $this->getForm($row);
        return $this->formDataTransformer->convertFlexFormContentToArray($row[$fieldName] ?? '', $form);
    }

    /**
     * Returns the page record with localisation applied, if any
     * exists in database. Maintains uid and pid of the original
     * page if localisation is applied.
     */
    protected function getPageValues(): array
    {
        $record = $GLOBALS['TSFE']->page ?? null;
        if (!$record) {
            return [];
        }
        return $record;
    }

    public function getTemplateVariables(array $row): array
    {
        $variables = array_merge(
            $this->templateVariables,
            $this->getViewVariables($row)
        );
        $variables['page'] = $this->getPageValues();
        $variables['user'] = $GLOBALS['TSFE']->fe_user->user ?? [];
        if (file_exists((string) $this->getTemplatePathAndFilename($row))) {
            $variables['grid'] = $this->getGrid($row);
            $variables['form'] = $this->getForm($row);
        }
        return $variables;
    }

    public function getConfigurationSectionName(array $row, ?string $forField = null): ?string
    {
        unset($row, $forField);
        return $this->configurationSectionName;
    }

    public function getExtensionKey(array $row, ?string $forField = null): string
    {
        unset($row);
        return $this->extensionKey;
    }

    public function getPriority(array $row): int
    {
        unset($row);
        return $this->priority;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPluginName(): ?string
    {
        return $this->pluginName;
    }

    public function setPluginName(?string $pluginName): self
    {
        $this->pluginName = $pluginName;
        return $this;
    }

    /**
     * Post-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
     * @param integer $id The ID of the current record (which is sometimes not included in $row)
     * @param array $row the record that was modified
     * @param DataHandler $reference A reference to the DataHandler object that modified the record
     * @param array $removals Allows overridden methods to pass an array of fields to remove from the stored Flux value
     * @return bool true to stop processing other providers, false to continue processing other providers.
     */
    public function postProcessRecord(
        string $operation,
        int $id,
        array $row,
        DataHandler $reference,
        array $removals = []
    ): bool {
        if (!in_array($operation, ['update', 'new'], true)) {
            return false;
        }

        $record = $reference->datamap[$this->tableName][$id] ?? $row;
        $tableName = (string) $this->getTableName($record);
        $fieldName = $this->getFieldName($record);

        $dontProcess = (
            $fieldName === null
            || !isset($record[$fieldName])
            || !isset($record[$fieldName]['data'])
            || !is_array($record[$fieldName]['data'])
        );
        if ($dontProcess) {
            return false;
        }

        $stored = $this->recordService->getSingle($tableName, '*', $id) ?? $record;

        $removals = array_merge(
            $removals,
            $this->extractFieldNamesToClear($record, $fieldName)
        );

        if (!empty($removals) && !empty($stored[$fieldName])) {
            $stored[$fieldName] = MiscellaneousUtility::cleanFlexFormXml($stored[$fieldName], $removals);
            $this->recordService->update($tableName, $stored);
        }

        return false;
    }

    protected function extractFieldNamesToClear(array $record, string $fieldName): array
    {
        $removals = [];
        $data = $record[$fieldName]['data'] ?? [];
        foreach ($data as $sheetName => $sheetFields) {
            foreach ($sheetFields['lDEF'] as $sheetFieldName => $fieldDefinition) {
                if ('_clear' === substr($sheetFieldName, -6)) {
                    $removals[] = $sheetFieldName;
                } else {
                    $clearFieldName = $sheetFieldName . '_clear';
                    if (isset($data[$sheetName]['lDEF'][$clearFieldName]['vDEF'])) {
                        if ((boolean) $data[$sheetName]['lDEF'][$clearFieldName]['vDEF']) {
                            $removals[] = $sheetFieldName;
                        }
                    }
                }
            }
        }
        return array_unique($removals);
    }

    /**
     * Post-process the TCEforms DataStructure for a record associated
     * with this ConfigurationProvider.
     */
    public function postProcessDataStructure(array &$row, ?array &$dataStructure, array $conf): void
    {
        $form = $this->getForm($row, $conf['fieldName'] ?? null);
        if ($dataStructure !== null && $form !== null) {
            $dataStructure = array_replace_recursive($dataStructure, $form->build());
        }
    }

    /**
     * Processes the table configuration (TCA) for the table associated
     * with this Provider, as determined by the trigger() method. Gets
     * passed an instance of the record being edited/created along with
     * the current configuration array - and must return a complete copy
     * of the configuration array manipulated to the Provider's needs.
     *
     * @param array $row The record being edited/created
     * @param array $configuration Current TCA configuration
     * @return array The large FormEngine configuration array - see FormEngine documentation!
     */
    public function processTableConfiguration(array $row, array $configuration): array
    {
        $form = $this->getForm($row);
        if (!$form) {
            return $configuration;
        }

        /** @var string $table */
        $table = $this->getTableName($row);
        $recordType = $configuration['recordTypeValue'];

        // Replace or add fields as native TCA fields if defined as native=1 in the Flux form:
        foreach ($form->getFields() as $fieldName => $field) {
            if (!$field instanceof Form\FieldInterface || !$field->isNative()) {
                continue;
            }

            // Basic initialization: declare the TCA field's data structure and initialize it in databaseRow.
            $configuration['processedTca']['columns'][$fieldName] = $field->build();
            if (!in_array($fieldName, $configuration['columnsToProcess'], true)) {
                $configuration['columnsToProcess'][] = $fieldName;
                $configuration['databaseRow'][$fieldName] = $configuration['databaseRow'][$fieldName]
                    ?? $field->getDefault();
            }

            // Handle potential positioning instructions.
            $positionOption = $field->getPosition();
            if (!empty($positionOption)) {
                $insertFieldDefinition = $fieldName;
                if (strpos($positionOption, ' ') !== false) {
                    [$position, $sheet] = explode(' ', $positionOption, 2);
                    $insertFieldDefinition = '--div--;' . $sheet . ',' . $fieldName;
                } else {
                    $position = $positionOption;
                }
                ExtensionManagementUtility::addToAllTCAtypes($table, $insertFieldDefinition, $recordType, $position);
                $configuration['processedTca']['types'][$recordType]['showitem']
                    = $GLOBALS['TCA'][$table]['types'][$recordType]['showitem'];
            }
        }

        // Remove any fields listed in the "hideNativeFields" Flux form option
        /** @var string|array $hideFieldsOption */
        $hideFieldsOption = $form->getOption(Form::OPTION_HIDE_NATIVE_FIELDS);
        if (!empty($hideFieldsOption)) {
            $hideFields = is_array($hideFieldsOption)
                ? $hideFieldsOption
                : GeneralUtility::trimExplode(',', $hideFieldsOption, true);
            foreach ($hideFields as $hideField) {
                unset($configuration['processedTca']['columns'][$hideField]);
            }
        }
        return $configuration;
    }

    protected function getViewForRecord(array $row, ?string $forField = null): ViewInterface
    {
        return $this->viewBuilder->buildTemplateView(
            $this->getControllerExtensionKeyFromRecord($row, $forField),
            $this->getControllerNameFromRecord($row),
            $this->getControllerActionFromRecord($row, $forField),
            $this->getPluginName() ?? $this->getControllerNameFromRecord($row),
            $this->getTemplatePathAndFilename($row, $forField)
        );
    }

    /**
     * Get preview chunks - header and content - as
     * [string $headerContent, string $previewContent, boolean $continueRendering]
     *
     * Default implementation renders the Preview section from the template
     * file that the actual Provider returns for $row, using paths also
     * determined by $row. Example: fluidcontent's Provider returns files
     * and paths based on selected "Fluid Content type" and inherits and
     * uses this method to render a Preview from the template file in the
     * specific path. This default implementation expects the TYPO3 core
     * to render the default header, so it returns NULL as $headerContent.
     */
    public function getPreview(array $row): array
    {
        $previewContent = $this->viewBuilder->buildPreviewView(
            $this->getControllerExtensionKeyFromRecord($row),
            $this->getControllerNameFromRecord($row),
            $this->getControllerActionFromRecord($row),
            $this->getPluginName() ?? $this->getControllerNameFromRecord($row),
            $this->getTemplatePathAndFilename($row)
        )->getPreview($this, $row);
        return [null, $previewContent, empty($previewContent)];
    }

    protected function getCacheKeyForStoredVariable(array $row, string $variable, ?string $forField = null): string
    {
        return implode(
            '-',
            [
                'flux',
                'storedvariable',
                $this->getTableName($row),
                $forField ?? $this->getFieldName($row),
                $row['uid'] ?? 0,
                $this->getControllerExtensionKeyFromRecord($row, $forField),
                $this->getControllerActionFromRecord($row, $forField),
                $variable
            ]
        );
    }

    /**
     * Stub: override this to return a controller name associated with $row.
     * Default strategy: return base name of Provider class minus the "Provider" suffix.
     */
    public function getControllerNameFromRecord(array $row): string
    {
        if (!empty($this->controllerName)) {
            return $this->controllerName;
        }
        $class = get_class($this);
        $separator = false !== strpos($class, '\\') ? '\\' : '_';
        $parts = explode($separator, $class);
        $base = end($parts);
        return substr($base, 0, -8);
    }

    /**
     * Stub: Get the extension key of the controller associated with $row
     */
    public function getControllerExtensionKeyFromRecord(array $row, ?string $forField = null): string
    {
        return $this->extensionKey;
    }

    /**
     * Stub: Get the package name of the controller associated with $row
     */
    public function getControllerPackageNameFromRecord(array $row, ?string $forField = null): string
    {
        $extensionKey = $this->getControllerExtensionKeyFromRecord($row, $forField);
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
        $vendor = ExtensionNamingUtility::getVendorName($extensionKey);
        return null !== $vendor ? $vendor . '.' . $extensionName : $extensionName;
    }

    /**
     * Stub: Get the name of the controller action associated with $row
     */
    public function getControllerActionFromRecord(array $row, ?string $forField = null): string
    {
        return $this->controllerAction;
    }

    /**
     * Stub: Get a compacted controller name + action name string
     */
    public function getControllerActionReferenceFromRecord(array $row, ?string $forField = null): string
    {
        return $this->getControllerNameFromRecord($row) . '->' . $this->getControllerActionFromRecord($row, $forField);
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function setFieldName(?string $fieldName): self
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function setExtensionKey(string $extensionKey): self
    {
        $this->extensionKey = $extensionKey;
        return $this;
    }

    public function setControllerName(string $controllerName): self
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    public function setControllerAction(string $controllerAction): self
    {
        $this->controllerAction = $controllerAction;
        return $this;
    }

    public function setTemplateVariables(?array $templateVariables): self
    {
        $this->templateVariables = $templateVariables ?? [];
        return $this;
    }

    public function setTemplatePathAndFilename(?string $templatePathAndFilename): self
    {
        $this->templatePathAndFilename = $templatePathAndFilename;
        return $this;
    }

    public function setTemplatePaths(?array $templatePaths): self
    {
        $this->templatePaths = $templatePaths;
        return $this;
    }

    public function setConfigurationSectionName(?string $configurationSectionName): self
    {
        $this->configurationSectionName = $configurationSectionName;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setForm(Form $form): self
    {
        $this->form = $form;
        return $this;
    }

    public function setGrid(Grid $grid): self
    {
        $this->grid = $grid;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function dispatchFlashMessageForException(\Throwable $error): void
    {
        /** @var FlashMessage $flashMesasage */
        $flashMesasage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $error->getMessage(),
            '',
            FlashMessage::ERROR
        );
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMesasageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $flashMesasageQueue->enqueue($flashMesasage);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function resolveAbsolutePathToFile(?string $file): ?string
    {
        return $file === null ? null : GeneralUtility::getFileAbsFileName($file);
    }

    /**
     * @param string|array $extensionKeyOrConfiguration
     * @codeCoverageIgnore
     */
    protected function createTemplatePaths($extensionKeyOrConfiguration): TemplatePaths
    {
        /** @var TemplatePaths $paths */
        $paths = GeneralUtility::makeInstance(TemplatePaths::class, $extensionKeyOrConfiguration);
        return $paths;
    }
}
