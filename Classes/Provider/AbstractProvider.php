<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Integration\ViewBuilder;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
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
    protected string $pluginName = '';
    protected ?string $controllerName = null;
    protected string $controllerAction = 'default';
    protected int $priority = 50;
    protected ?Form $form = null;
    protected ?Grid $grid = null;

    protected FluxService $configurationService;
    protected WorkspacesAwareRecordService $recordService;
    protected ViewBuilder $viewBuilder;

    public function __construct()
    {
        /** @var FluxService $configurationService */
        $configurationService = GeneralUtility::makeInstance(FluxService::class);
        $this->configurationService = $configurationService;

        /** @var WorkspacesAwareRecordService $recordService */
        $recordService = GeneralUtility::makeInstance(WorkspacesAwareRecordService::class);
        $this->recordService = $recordService;

        /** @var ViewBuilder $viewBuilder */
        $viewBuilder = GeneralUtility::makeInstance(ViewBuilder::class);
        $this->viewBuilder = $viewBuilder;
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
        $rowIsEmpty = (0 === count($row));
        $matchesContentType = $contentTypeFromRecord === $contentObjectType;
        $matchesPluginType = $rowContainsPlugin && $pluginTypeFromRecord === $listType;
        $matchesTableName = ($providerTableName === $table || !$table);
        $matchesFieldName = ($providerFieldName === $field || !$field);
        $matchesExtensionKey = ($providerExtensionKey === $extensionKey || !$extensionKey);
        $isFullMatch = $matchesExtensionKey && $matchesTableName && $matchesFieldName
            && ($matchesContentType || ($rowContainsPlugin && $matchesPluginType));
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
    protected function resolveFormClassName(array $row): ?string
    {
        $packageName = $this->getControllerPackageNameFromRecord($row);
        $packageKey = str_replace('.', '\\', $packageName);
        $controllerName = $this->getControllerNameFromRecord($row);
        $action = $this->getControllerActionFromRecord($row);
        $expectedClassName = sprintf(static::FORM_CLASS_PATTERN, $packageKey, $controllerName, ucfirst($action));
        return class_exists($expectedClassName) ? $expectedClassName : null;
    }

    protected function getViewVariables(array $row): array
    {
        $extensionKey = (string) $this->getExtensionKey($row);
        $fieldName = $this->getFieldName($row);
        $variables = [
            'record' => $row,
            'settings' => $this->configurationService->getSettingsForExtensionName($extensionKey)
        ];

        // Special case: when saving a new record variable $row[$fieldName] is already an array
        // and must not be processed by the configuration service. This has limited support from
        // Flux (essentially: no Form instance which means no inheritance, transformation or
        // form options can be dependended upon at this stage).
        if (isset($row[$fieldName]) && !is_array($row[$fieldName])) {
            $recordVariables = $this->configurationService->convertFlexFormContentToArray($row[$fieldName]);
            $variables = RecursiveArrayUtility::mergeRecursiveOverrule($variables, $recordVariables);
        }

        $variables = RecursiveArrayUtility::mergeRecursiveOverrule($this->templateVariables, $variables);

        return $variables;
    }

    public function getForm(array $row): ?Form
    {
        /** @var Form $form */
        $form = $this->form
            ?? $this->createCustomFormInstance($row)
            ?? $this->extractConfiguration($row, 'form')
            ?? Form::create();
        $form->setOption(Form::OPTION_RECORD, $row);
        return $form;
    }

    protected function createCustomFormInstance(array $row): ?Form
    {
        $formClassName = $this->resolveFormClassName($row);
        if ($formClassName !== null && class_exists($formClassName)) {
            return $formClassName::create(['row']);
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
                        $gridColumn->setColumnPosition($object['colPos']);
                    }
                } elseif ($container->getGridMode() === Form\Container\Section::GRID_MODE_COLUMNS) {
                    $gridRow = $grid->createContainer(Form\Container\Row::class, 'row');
                    foreach ($persistedObjects as $index => $object) {
                        $gridColumn = $gridRow->createContainer(
                            Form\Container\Column::class,
                            'column' . $object['colPos'],
                            $object['label'] ?? 'Column ' . $object['colPos']
                        );
                        $gridColumn->setColumnPosition($object['colPos']);
                        $gridColumn->setColSpan($object['colspan'] ?? 1);
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
    protected function extractConfiguration(array $row, ?string $name = null)
    {
        $cacheKeyAll = $this->getCacheKeyForStoredVariable($row, '_all');
        /** @var array $allCached */
        $allCached = $this->configurationService->getFromCaches($cacheKeyAll);
        $fromCache = $allCached[$name] ?? null;
        if ($fromCache) {
            return $fromCache;
        }
        $configurationSectionName = $this->getConfigurationSectionName($row);
        $viewVariables = $this->getViewVariables($row);
        $view = $this->getViewForRecord($row);
        $view->getRenderingContext()->getViewHelperVariableContainer()->addOrUpdate(
            FormViewHelper::class,
            FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME,
            $this->getExtensionKey($row)
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
            $variables['form']->setOption(Form::OPTION_TEMPLATEFILE, $this->getTemplatePathAndFilename($row));
            if ($variables['form']->getOption(Form::OPTION_STATIC)) {
                $this->configurationService->setInCaches($variables, true, $cacheKeyAll);
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

    public function getTemplatePathAndFilename(array $row): ?string
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
    public function getFlexFormValues(array $row): array
    {
        $fieldName = $this->getFieldName($row);
        $form = $this->getForm($row);
        return $this->configurationService->convertFlexFormContentToArray($row[$fieldName] ?? '', $form);
    }

    /**
     * Gets the current language name as string, in a format that is
     * compatible with language pointers in a flexform. Usually this
     * implies values like "en", "de" etc.
     *
     * Return NULL when language is site default language.
     */
    protected function getCurrentLanguageName(): ?string
    {
        $language = $GLOBALS['TSFE']->lang;
        if (true === empty($language) || 'default' === $language) {
            $language = null;
        }
        return $language;
    }

    /**
     * Gets the pointer name to use whne retrieving values from a
     * flexform source. Return NULL when pointer is default.
     */
    protected function getCurrentValuePointerName(): ?string
    {
        return $this->getCurrentLanguageName();
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
        $variables = (array) $this->templateVariables;
        $variables['record'] = $row;
        $variables['page'] = $this->getPageValues();
        $variables['user'] = $GLOBALS['TSFE']->fe_user->user ?? [];
        if (file_exists((string) $this->getTemplatePathAndFilename($row))) {
            $variables['grid'] = $this->getGrid($row);
            $variables['form'] = $this->getForm($row);
        }
        return $variables;
    }

    public function getConfigurationSectionName(array $row): ?string
    {
        unset($row);
        return $this->configurationSectionName;
    }

    public function getExtensionKey(array $row): string
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

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function setPluginName(string $pluginName): self
    {
        $this->pluginName = $pluginName;
        return $this;
    }

    /**
     * Pre-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param array $row The record by reference. Changing fields' values changes the record's values before display
     * @param integer $id The ID of the current record (which is sometimes not included in $row
     * @param DataHandler $reference A reference to the DataHandler object that is currently displaying the record
     */
    public function preProcessRecord(array &$row, int $id, DataHandler $reference): void
    {
        // TODO: move to single-fire implementation in TceMain (DataHandler)
        // TODO: remove in Flux 10.0
        $fieldName = $this->getFieldName($row);
        if ($fieldName === null) {
            return;
        }
        $tableName = (string) $this->getTableName($row);
        if (is_array($row[$fieldName]) && isset($row[$fieldName]['data']['options']['lDEF'])
            && is_array($row[$fieldName]['data']['options']['lDEF'])) {
            foreach ($row[$fieldName]['data']['options']['lDEF'] as $key => $value) {
                if (0 === strpos($key, $tableName)) {
                    $parts = explode('.', $key);
                    $realKey = array_pop($parts);
                    if (isset($GLOBALS['TCA'][$tableName]['columns'][$realKey])) {
                        $row[$realKey] = $value['vDEF'];
                    }
                }
            }
        }
    }

    /**
     * Post-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
     * @param integer $id The ID of the current record (which is sometimes not included in $row
     * @param array $row the record by reference. Changing fields' values changes the record's values just before saving
     * @param DataHandler $reference A reference to the DataHandler object that is currently saving the record
     * @param array $removals Allows overridden methods to pass an array of fields to remove from the stored Flux value
     * @return void
     */
    public function postProcessRecord(
        string $operation,
        int $id,
        array &$row,
        DataHandler $reference,
        array $removals = []
    ): void {
        // TODO: move to single-fire implementation in TceMain (DataHandler)
        // TODO: remove in Flux 10.0
        if ('update' === $operation || 'new' === $operation) {
            $tableName = (string) $this->getTableName($row);
            $record = $reference->datamap[$this->tableName][$id] ?? [];
            $stored = $this->recordService->getSingle($tableName, '*', $record['uid'] ?? 0) ?? $record;
            $fieldName = $this->getFieldName((array) $record);
            $dontProcess = (
                null === $fieldName
                || false === isset($row[$fieldName])
                || false === isset($record[$fieldName]['data'])
                || false === is_array($record[$fieldName]['data'])
            );
            if (true === $dontProcess) {
                return;
            }
            $data = $record[$fieldName]['data'];
            foreach ($data as $sheetName => $sheetFields) {
                foreach ($sheetFields['lDEF'] as $sheetFieldName => $fieldDefinition) {
                    if ('_clear' === substr($sheetFieldName, -6)) {
                        array_push($removals, $sheetFieldName);
                    } else {
                        $clearFieldName = $sheetFieldName . '_clear';
                        if (isset($data[$sheetName]['lDEF'][$clearFieldName]['vDEF'])) {
                            if ((boolean) $data[$sheetName]['lDEF'][$clearFieldName]['vDEF']) {
                                array_push($removals, $sheetFieldName);
                            }
                        }
                    }
                }
            }
            $stored[$fieldName] = MiscellaneousUtility::cleanFlexFormXml($row[$fieldName], $removals);
            $row[$fieldName] = $stored[$fieldName];
            $reference->datamap[$this->tableName][$id][$fieldName] = $row[$fieldName];
            if ($stored['uid']) {
                $this->recordService->update($tableName, $stored);
            }
        }
    }

    /**
     * Post-process database operation for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param string $status TYPO3 operation identifier, i.e. "new" etc.
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param array $row The record by reference. Changing fields' values changes the record's values just
     *                   before saving after operation
     * @param DataHandler $reference A reference to the DataHandler object that is currently performing the operation
     * @codeCoverageIgnore
     */
    public function postProcessDatabaseOperation(string $status, int $id, array &$row, DataHandler $reference): void
    {
        // TODO: move function body to single-fire implementation in TceMain (DataHandler)
        // TODO: remove in Flux 10.0
        // We dispatch the Outlet associated with the Form, triggering each defined
        // Pipe inside the Outlet to "conduct" the data.
        $record = $this->recordService->getSingle((string) $this->getTableName($row), '*', $id);
        if (null !== $record) {
            $form = $this->getForm($record);
            if (true === $form instanceof Form\FormInterface) {
                $form->getOutlet()->fill([
                    'command' => $status,
                    'uid' => $id,
                    'record' => $row,
                    'table' => $this->getTableName($record),
                    'provider' => $this,
                    'dataHandler' => $reference
                ]);
            }
        }
    }

    /**
     * Post-process the TCEforms DataStructure for a record associated
     * with this ConfigurationProvider.
     */
    public function postProcessDataStructure(array &$row, ?array &$dataStructure, array $conf): void
    {
        $form = $this->getForm($row);
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
        return $configuration;
    }

    public function getViewForRecord(array $row): ViewInterface
    {
        return $this->viewBuilder->buildTemplateView(
            $this->getControllerExtensionKeyFromRecord($row),
            $this->getControllerNameFromRecord($row),
            $this->getControllerActionFromRecord($row),
            $this->getTemplatePathAndFilename($row)
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
            $this->getTemplatePathAndFilename($row)
        )->getPreview($this, $row);
        return [null, $previewContent, empty($previewContent)];
    }

    protected function getCacheKeyForStoredVariable(array $row, string $variable): string
    {
        return implode(
            '-',
            [
                'flux',
                'storedvariable',
                $this->getTableName($row),
                $this->getFieldName($row),
                $row['uid'] ?? 0,
                $this->getControllerExtensionKeyFromRecord($row),
                $this->getControllerActionFromRecord($row),
                $variable
            ]
        );
    }

    /**
     * Stub: override this to return a controller action name associated with $row.
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
    public function getControllerExtensionKeyFromRecord(array $row): string
    {
        return $this->extensionKey;
    }

    /**
     * Stub: Get the package name of the controller associated with $row
     */
    public function getControllerPackageNameFromRecord(array $row): string
    {
        $extensionKey = $this->getControllerExtensionKeyFromRecord($row);
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
        $vendor = ExtensionNamingUtility::getVendorName($extensionKey);
        return null !== $vendor ? $vendor . '.' . $extensionName : $extensionName;
    }

    /**
     * Stub: Get the name of the controller action associated with $row
     */
    public function getControllerActionFromRecord(array $row): string
    {
        return $this->controllerAction;
    }

    /**
     * Stub: Get a compacted controller name + action name string
     */
    public function getControllerActionReferenceFromRecord(array $row): string
    {
        return $this->getControllerNameFromRecord($row) . '->' . $this->getControllerActionFromRecord($row);
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
