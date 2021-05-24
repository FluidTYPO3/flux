<?php
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
use FluidTYPO3\Flux\Integration\PreviewView;
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
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request as WebRequest;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * AbstractProvider
 */
class AbstractProvider implements ProviderInterface
{

    const FORM_CLASS_PATTERN = '%s\\Form\\%s\\%sForm';

    const CONTENT_OBJECT_TYPE_LIST = 'list';

    /**
     * @var string
     */
    protected $name = null;

    /**
     * Fill with the table column name which should trigger this Provider.
     *
     * @var string
     */
    protected $fieldName = null;

    /**
     * Fill with the name of the DB table which should trigger this Provider.
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * Fill with the "list_type" value that should trigger this Provider.
     *
     * @var string
     */
    protected $listType = null;

    /**
     * Fill with the "CType" value that should trigger this Provider.
     *
     * @var string
     */
    protected $contentObjectType = null;

    /**
     * @var string
     */
    protected $parentFieldName = null;

    /**
     * @var array|NULL
     */
    protected $row = null;

    /**
     * @var string|NULL
     */
    protected $templatePathAndFilename = null;

    /**
     * @var array
     */
    protected $templateVariables = [];

    /**
     * @var array|NULL
     */
    protected $templatePaths = null;

    /**
     * @var string|NULL
     */
    protected $configurationSectionName = 'Configuration';

    /**
     * @var string|NULL
     */
    protected $extensionKey = null;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $controllerAction = 'default';

    /**
     * @var integer
     */
    protected $priority = 50;

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var Grid
     */
    protected $grid = null;

    /**
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var WorkspacesAwareRecordService
     */
    protected $recordService;

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @param WorkspacesAwareRecordService $recordService
     * @return void
     */
    public function injectRecordService(WorkspacesAwareRecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * @param array $settings
     * @return void
     */
    public function loadSettings(array $settings)
    {
        if (true === isset($settings['name'])) {
            $this->setName($settings['name']);
        }
        if (true === isset($settings['form'])) {
            $form = Form::create($settings['form']);
            if (true === isset($settings['extensionKey'])) {
                $extensionKey = $settings['extensionKey'];
                $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
                $form->setExtensionName($extensionName);
            }
            $settings['form'] = $form;
        }
        if (true === isset($settings['grid'])) {
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

    /**
     * @param array $row
     * @param string $table
     * @param string $field
     * @param string $extensionKey
     * @return boolean
     */
    public function trigger(array $row, $table, $field, $extensionKey = null)
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
     * @param array $row
     * @return string
     */
    protected function resolveFormClassName(array $row)
    {
        $packageName = $this->getControllerPackageNameFromRecord($row);
        $packageKey = str_replace('.', '\\', $packageName);
        $controllerName = $this->getControllerNameFromRecord($row);
        $action = $this->getControllerActionFromRecord($row);
        $expectedClassName = sprintf(static::FORM_CLASS_PATTERN, $packageKey, $controllerName, ucfirst($action));
        return true === class_exists($expectedClassName) ? $expectedClassName : null;
    }

    /**
     * @param array $row
     * @return array
     */
    protected function getViewVariables(array $row)
    {
        $extensionKey = $this->getExtensionKey($row);
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

    /**
     * @param array $row
     * @return Form|NULL
     */
    public function getForm(array $row)
    {
        $form = $this->form
            ?? $this->createCustomFormInstance($row)
            ?? $this->extractConfiguration($row, 'form')
            ?? Form::create();
        $form->setOption(Form::OPTION_RECORD, $row);
        return $form;
    }

    /**
     * @param array $row
     * @return Form|null
     */
    protected function createCustomFormInstance(array $row)
    {
        $formClassName = $this->resolveFormClassName($row);
        if (class_exists($formClassName)) {
            return $formClassName::create(['row']);
        }
        return null;
    }

    /**
     * @param array $row
     * @return Grid
     */
    public function getGrid(array $row)
    {
        $form = $this->getForm($row);
        if ($form) {
            $container = $this->detectContentContainerParent($form);
            if ($container) {
                $values = $this->getFlexFormValues($row);
                $persistedObjects = array_column(
                    ObjectAccess::getProperty($values, $container->getName()) ?? [],
                    $container->getContentContainer()->getName()
                );


                // Determine the mode to render, then create an ad-hoc grid.
                $grid = Grid::create();
                if ($container->getGridMode() === Form\Container\Section::GRID_MODE_ROWS) {
                    foreach ($persistedObjects as $index => $object) {
                        $gridRow = $grid->createContainer('Row', 'row' . $index);
                        $gridColumn = $gridRow->createContainer(
                            'Column',
                            'column' . $object['colPos'],
                            $object['label'] ?? 'Column ' . $object['colPos']
                        );
                        $gridColumn->setColumnPosition($object['colPos']);
                    }
                } elseif ($container->getGridMode() === Form\Container\Section::GRID_MODE_COLUMNS) {
                    $gridRow = $grid->createContainer('Row', 'row');
                    foreach ($persistedObjects as $index => $object) {
                        $gridColumn = $gridRow->createContainer(
                            'Column',
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
        $grid = $this->grid ?? $this->extractConfiguration($row, 'grids')['grid'] ?? Grid::create();
        $grid->setExtensionName($grid->getExtensionName() ?: $this->getControllerExtensionKeyFromRecord($row));
        return $grid;
    }

    /**
     * @param Form\ContainerInterface $container
     * @return Form\Container\Section|null
     */
    protected function detectContentContainerParent(Form\ContainerInterface $container)
    {
        if ($container instanceof Form\Container\SectionObject && $container->isContentContainer()) {
            return $container->getParent();
        }
        foreach ($container->getChildren() as $child) {
            if ($child instanceof Form\ContainerInterface && ($detected = $this->detectContentContainerParent($child))) {
                return $detected;
            }
        }
        return null;
    }

    /**
     * @param array $row
     * @param string|null $name
     * @return mixed|null
     */
    protected function extractConfiguration(array $row, $name = null)
    {
        $cacheKeyAll = $this->getCacheKeyForStoredVariable($row, '_all');
        $allCached = $this->configurationService->getFromCaches($cacheKeyAll);
        $fromCache = $allCached[$name] ?? null;
        if ($fromCache) {
            return $fromCache;
        }
        $configurationSectionName = $this->getConfigurationSectionName($row);
        $viewVariables = $this->getViewVariables($row);
        $view = $this->getViewForRecord($row);

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

        $variables = $view->getRenderingContext()->getViewHelperVariableContainer()->getAll(FormViewHelper::class) ?? [];
        if (isset($variables['form'])) {
            $variables['form']->setOption(Form::OPTION_TEMPLATEFILE, $this->getTemplatePathAndFilename($row));
            if ($variables['form']->getOption(Form::OPTION_STATIC)) {
                $this->configurationService->setInCaches($variables, true, $cacheKeyAll);
            }
        }

        $returnValue = $name ? $variables[$name] : $variables;

        return HookHandler::trigger(
            HookHandler::PROVIDER_EXTRACTED_OBJECT,
            [
                'record' => $row,
                'name' => $name,
                'value' => $returnValue
            ]
        )['value'];
    }

    /**
     * @param string $listType
     * @return ProviderInterface
     */
    public function setListType($listType)
    {
        $this->listType = $listType;
        return $this;
    }

    /**
     * @return string
     */
    public function getListType()
    {
        return $this->listType;
    }

    /**
     * @param string $contentObjectType
     */
    public function setContentObjectType($contentObjectType)
    {
        $this->contentObjectType = $contentObjectType;
    }

    /**
     * @return string
     */
    public function getContentObjectType()
    {
        return $this->contentObjectType;
    }

    /**
     * @param array $row The record row which triggered processing
     * @return string|NULL
     */
    public function getFieldName(array $row)
    {
        return $this->fieldName;
    }

    /**
     * @param array $row
     * @return string
     */
    public function getParentFieldName(array $row)
    {
        unset($row);
        return $this->parentFieldName;
    }

    /**
     * @param array $row The record row which triggered processing
     * @return string|NULL
     */
    public function getTableName(array $row)
    {
        unset($row);
        return $this->tableName;
    }

    /**
     * @param array $row
     * @return string|NULL
     */
    public function getTemplatePathAndFilename(array $row)
    {
        $templatePathAndFilename = $this->templatePathAndFilename;
        if (!PathUtility::isAbsolutePath($templatePathAndFilename)) {
            $templatePathAndFilename = GeneralUtility::getFileAbsFileName($templatePathAndFilename);
            if (true === empty($templatePathAndFilename)) {
                $templatePathAndFilename = null;
            }
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
     *
     * @param array $row
     * @return array
     */
    public function getFlexFormValues(array $row)
    {
        $fieldName = $this->getFieldName($row);
        $form = $this->getForm($row);
        return $this->configurationService->convertFlexFormContentToArray($row[$fieldName], $form);
    }

    /**
     * Gets the current language name as string, in a format that is
     * compatible with language pointers in a flexform. Usually this
     * implies values like "en", "de" etc.
     *
     * Return NULL when language is site default language.
     *
     * @return string|NULL
     */
    protected function getCurrentLanguageName()
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
     *
     * @return string|NULL
     */
    protected function getCurrentValuePointerName()
    {
        return $this->getCurrentLanguageName();
    }

    /**
     * Returns the page record with localisation applied, if any
     * exists in database. Maintains uid and pid of the original
     * page if localisation is applied.
     *
     * @return array
     */
    protected function getPageValues()
    {
        $record = $GLOBALS['TSFE']->page ?? null;
        if (!$record) {
            return [];
        }
        return $record;
    }

    /**
     * @param array $row
     * @return array|NULL
     */
    public function getTemplateVariables(array $row)
    {
        $variables = (array) $this->templateVariables;
        $variables['record'] = $row;
        $variables['page'] = $this->getPageValues();
        $variables['user'] = $GLOBALS['TSFE']->fe_user->user ?? [];
        if (file_exists($this->getTemplatePathAndFilename($row))) {
            $variables['grid'] = $this->getGrid($row);
            $variables['form'] = $this->getForm($row);
        }
        return $variables;
    }

    /**
     * @param array $row
     * @return string|NULL
     */
    public function getConfigurationSectionName(array $row)
    {
        unset($row);
        return $this->configurationSectionName;
    }

    /**
     * @param array $row
     * @return string|NULL
     */
    public function getExtensionKey(array $row)
    {
        unset($row);
        return $this->extensionKey;
    }

    /**
     * @param array $row
     * @return integer
     */
    public function getPriority(array $row)
    {
        unset($row);
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Pre-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param array $row The record by reference. Changing fields' values changes the record's values before display
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param DataHandler $reference A reference to the DataHandler object that is currently displaying the record
     * @return void
     */
    public function preProcessRecord(array &$row, $id, DataHandler $reference)
    {
        // TODO: move to single-fire implementation in TceMain (DataHandler)
        $fieldName = $this->getFieldName($row);
        $tableName = $this->getTableName($row);
        if (is_array($row[$fieldName]) && isset($row[$fieldName]['data']['options']['lDEF'])
            && is_array($row[$fieldName]['data']['options']['lDEF'])) {
            foreach ($row[$fieldName]['data']['options']['lDEF'] as $key => $value) {
                if (0 === strpos($key, $tableName)) {
                    $realKey = array_pop(explode('.', $key));
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
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param array $row the record by reference. Changing fields' values changes the record's values just before saving
     * @param DataHandler $reference A reference to the DataHandler object that is currently saving the record
     * @param array $removals Allows overridden methods to pass an array of fields to remove from the stored Flux value
     * @return void
     */
    public function postProcessRecord($operation, $id, array &$row, DataHandler $reference, array $removals = [])
    {
        // TODO: move to single-fire implementation in TceMain (DataHandler)
        if ('update' === $operation || 'new' === $operation) {
            $record = $reference->datamap[$this->tableName][$id];
            $stored = $this->recordService->getSingle($this->tableName, '*', $record['uid']) ?? $record;
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
                $this->recordService->update($this->tableName, $stored);
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
     * @return void
     */
    public function postProcessDatabaseOperation($status, $id, &$row, DataHandler $reference)
    {
        // TODO: move function body to single-fire implementation in TceMain (DataHandler)
        // TODO: remove in Flux 10.0
        // We dispatch the Outlet associated with the Form, triggering each defined
        // Pipe inside the Outlet to "conduct" the data.
        $record = $this->recordService->getSingle($this->getTableName($row), '*', $id);
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
     * Pre-process a command executed on a record form the table this ConfigurationProvider
     * is attached to.
     *
     * @param string $command
     * @param integer $id
     * @param array $row
     * @param integer $relativeTo
     * @param DataHandler $reference
     * @return void
     */
    public function preProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference)
    {
        // TODO: remove in Flux 10.0
        unset($command, $id, $row, $relativeTo, $reference);
    }

    /**
     * Post-process a command executed on a record form the table this ConfigurationProvider
     * is attached to.
     *
     * @param string $command
     * @param integer $id
     * @param array $row
     * @param integer $relativeTo
     * @param DataHandler $reference
     * @return void
     */
    public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference)
    {
        // TODO: remove in Flux 10.0
        unset($command, $id, $row, $relativeTo, $reference);
    }

    /**
     * Post-process the TCEforms DataStructure for a record associated
     * with this ConfigurationProvider
     *
     * @param array $row
     * @param mixed $dataStructure
     * @param array $conf
     * @return void
     */
    public function postProcessDataStructure(array &$row, &$dataStructure, array $conf)
    {
        $defaultDataStructure = ['sheets' => ['sDEF' => ['ROOT' => ['type' => 'array', 'el' => ['xmlTitle' => ['TCEforms' => ['label' => 'The Title:', 'config' => ['type' => 'input', 'size' => '48']]]]]]]];
        $form = $this->getForm($row);
        if (null !== $form) {
            $newDataStructure = $form->build();
            if ($dataStructure === $defaultDataStructure) {
                $dataStructure = $newDataStructure;
            } elseif (count($form->getFields()) > 0) {
                if ($newDataStructure !== ['meta' => ['langDisable' => 1, 'langChildren' => 0], 'ROOT' => ['type' => 'array', 'el' => []]]) {
                    $dataStructure = array_replace_recursive($dataStructure, $newDataStructure);
                } else {
                    $dataStructure = $newDataStructure;
                }
            }
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
     * @param array $configuration
     * @return array The large FormEngine configuration array - see FormEngine documentation!
     */
    public function processTableConfiguration(array $row, array $configuration)
    {
        return $configuration;
    }

    /**
     * Perform various cleanup operations upon clearing cache
     *
     * @param array $command
     * @return void
     */
    public function clearCacheCommand($command = [])
    {
        // TODO: remove in Flux 10.0
    }

    /**
     * @param array $row
     * @param string $viewClassName
     * @return TemplateView
     */
    public function getViewForRecord(array $row, $viewClassName = TemplateView::class)
    {
        $controllerExtensionKey = $this->getControllerExtensionKeyFromRecord($row);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var WebRequest $request */
        $request = $objectManager->get(WebRequest::class);
        if (method_exists($request, 'setRequestUri')) {
            $request->setRequestUri(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
            $request->setBaseUri(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
        }
        $request->setControllerExtensionName(ExtensionNamingUtility::getExtensionName($controllerExtensionKey));
        $request->setControllerActionName($this->getControllerActionFromRecord($row));
        $request->setControllerName($this->getControllerNameFromRecord($row));
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($request);
        /** @var ControllerContext $controllerContext */
        $controllerContext = $objectManager->get(ControllerContext::class);
        $controllerContext->setRequest($request);
        $controllerContext->setUriBuilder($uriBuilder);
        $renderingContext = $objectManager->get(RenderingContext::class);
        $renderingContext->setControllerContext($controllerContext);
        $renderingContext->getTemplatePaths()->fillDefaultsByPackageName(ExtensionNamingUtility::getExtensionKey($controllerExtensionKey));
        $renderingContext->getTemplatePaths()->setTemplatePathAndFilename($this->getTemplatePathAndFilename($row));
        $renderingContext->setControllerName($this->getControllerNameFromRecord($row));
        $renderingContext->setControllerAction($this->getControllerActionFromRecord($row));
        /** @var TemplateView $view */
        $view = GeneralUtility::makeInstance(ObjectManager::class)->get($viewClassName);
        $view->setRenderingContext($renderingContext);
        return $view;
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
     *
     * @param array $row The record data to be analysed for variables to use in a rendered preview
     * @return array
     */
    public function getPreview(array $row)
    {
        $previewContent = $this->getViewForRecord($row, PreviewView::class)->getPreview(
            $this,
            $row
        );
        return [null, $previewContent, empty($previewContent)];
    }

    /**
     * @param array $row
     * @param string $variable
     * @return string
     */
    protected function getCacheKeyForStoredVariable(array $row, $variable)
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
     *
     * @param array $row
     * @return string
     */
    public function getControllerNameFromRecord(array $row)
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
     *
     * @param array $row
     * @return string
     */
    public function getControllerExtensionKeyFromRecord(array $row)
    {
        return $this->extensionKey;
    }

    /**
     * Stub: Get the package name of the controller associated with $row
     *
     * @param array $row
     * @return string
     */
    public function getControllerPackageNameFromRecord(array $row)
    {
        $extensionKey = $this->getControllerExtensionKeyFromRecord($row);
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
        $vendor = ExtensionNamingUtility::getVendorName($extensionKey);
        return null !== $vendor ? $vendor . '.' . $extensionName : $extensionName;
    }

    /**
     * Stub: Get the name of the controller action associated with $row
     *
     * @param array $row
     * @return string
     */
    public function getControllerActionFromRecord(array $row)
    {
        return $this->controllerAction;
    }

    /**
     * Stub: Get a compacted controller name + action name string
     *
     * @param array $row
     * @return string
     */
    public function getControllerActionReferenceFromRecord(array $row)
    {
        return $this->getControllerNameFromRecord($row) . '->' . $this->getControllerActionFromRecord($row);
    }

    /**
     * @param string $tableName
     * @return ProviderInterface
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @param string $fieldName
     * @return ProviderInterface
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * @param string $extensionKey
     * @return ProviderInterface
     */
    public function setExtensionKey($extensionKey)
    {
        $this->extensionKey = $extensionKey;
        return $this;
    }

    /**
     * @param $controllerName
     * @return ProviderInterface
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    /**
     * @param $controllerAction
     * @return ProviderInterface
     */
    public function setControllerAction($controllerAction)
    {
        $this->controllerAction = $controllerAction;
        return $this;
    }

    /**
     * @param array|NULL $templateVariables
     * @return ProviderInterface
     */
    public function setTemplateVariables($templateVariables)
    {
        $this->templateVariables = $templateVariables;
        return $this;
    }

    /**
     * @param string $templatePathAndFilename
     * @return ProviderInterface
     */
    public function setTemplatePathAndFilename($templatePathAndFilename)
    {
        $this->templatePathAndFilename = $templatePathAndFilename;
        return $this;
    }

    /**
     * @param array|NULL $templatePaths
     * @return ProviderInterface
     */
    public function setTemplatePaths($templatePaths)
    {
        $this->templatePaths = $templatePaths;
        return $this;
    }

    /**
     * @param string|NULL $configurationSectionName
     * @return ProviderInterface
     */
    public function setConfigurationSectionName($configurationSectionName)
    {
        $this->configurationSectionName = $configurationSectionName;
        return $this;
    }

    /**
     * @param string $name
     * @return ProviderInterface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Form $form
     * @return ProviderInterface
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param Grid $grid
     * @return ProviderInterface
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;
        return $this;
    }

    protected function dispatchFlashMessageForException(\Throwable $error)
    {
        $flashMesasage = GeneralUtility::makeInstance(FlashMessage::class, $error->getMessage(), '', FlashMessage::ERROR);
        $flashMesasageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier();
        $flashMesasageQueue->enqueue($flashMesasage);
    }
}
