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
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\PathUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\View\PreviewView;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
     * @var ViewContext
     * @deprecated To be removed in next major release
     */
    protected $viewContext;

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
        $rowContainsPlugin = (!empty($row['CType']) && self::CONTENT_OBJECT_TYPE_LIST === $row['CType']);
        $rowIsEmpty = (0 === count($row));
        $matchesContentType = ((empty($contentObjectType) && empty($row['CType']))
            || (!empty($row['CType']) && $row['CType'] === $contentObjectType));
        $matchesPluginType = ((!empty($row['list_type']) && $row['list_type'] === $listType));
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
        $expectedClassName = sprintf(self::FORM_CLASS_PATTERN, $packageKey, $controllerName, ucfirst($action));
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
        if (false === is_array($row[$fieldName])) {
            $recordVariables = $this->configurationService->convertFlexFormContentToArray(
                $row[$fieldName],
                null,
                'lDEF',
                'vDEF'
            );
            $variables = RecursiveArrayUtility::mergeRecursiveOverrule($variables, $recordVariables);
        }

        $variables = RecursiveArrayUtility::mergeRecursiveOverrule($this->templateVariables, $variables);

        return $variables;
    }

    /**
     * @param array $row
     * @param RequestInterface|null $request
     * @return ViewContext
     */
    public function getViewContext(array $row, RequestInterface $request = null)
    {
        GeneralUtility::logDeprecatedFunction();
        if (false === $this->viewContext instanceof ViewContext) {
            // Note: we do *not* store a local property because we do *not* want this function
            // to re-use the ViewContext unless explicitly set from the outside or initialised
            // by a sub-class.
            $context = new ViewContext(
                $this->getTemplatePathAndFilename($row),
                $this->getControllerPackageNameFromRecord($row),
                $this->getControllerNameFromRecord($row),
                $request
            );
            $context->setSectionName($this->getConfigurationSectionName($row));
            $context->setTemplatePaths(new TemplatePaths($this->getTemplatePaths($row)));
            $context->setVariables($this->getViewVariables($row));
            return $context;
        }
        return $this->viewContext;
    }

    /**
     * @param ViewContext $viewContext
     * @return ProviderInterface
     */
    public function setViewContext(ViewContext $viewContext)
    {
        $this->viewContext = $viewContext;
        return $this;
    }

    /**
     * @param array $row
     * @return Form|NULL
     */
    public function getForm(array $row)
    {
        if (null !== $this->form) {
            return $this->form;
        }
        $formName = 'form';
        $cacheKey = $this->getCacheKeyForStoredVariable($row, $formName);

        $form = null;
        $formClassName = $this->resolveFormClassName($row);
        if ($formClassName) {
            return call_user_func_array([$formClassName, 'create'], [$row]);
        } else {
            $fromCache = $this->configurationService->getFromCaches($cacheKey);
            if ($fromCache) {
                return $fromCache;
            }
            $viewContext = $this->getViewContext($row);
            if (null !== $viewContext->getTemplatePathAndFilename()) {
                $view = $this->configurationService->getPreparedExposedTemplateView($viewContext);
                $form = $view->getForm($viewContext->getSectionName(), $formName);
            }
        }

        if ($form) {
            $form->setOption(Form::OPTION_RECORD, $row);
            $form->setOption(Form::OPTION_RECORD_TABLE, $this->getTableName($row));
            $form->setOption(Form::OPTION_RECORD_FIELD, $this->getFieldName($row));
            $this->configurationService->setInCaches($form, $form->getOption(Form::OPTION_STATIC), $cacheKey);
        }

        return $form;
    }

    /**
     * @param array $row
     * @return Grid
     */
    public function getGrid(array $row)
    {
        if (null !== $this->grid) {
            return $this->grid;
        }
        $gridName = 'grid';
        $cacheKey = $this->getCacheKeyForStoredVariable($row, $gridName);
        $fromCache = $this->configurationService->getFromCaches($cacheKey);
        if ($fromCache) {
            return $fromCache;
        }

        $viewContext = $this->getViewContext($row);
        $form = $this->getForm($row);
        $grid = $this->configurationService->getGridFromTemplateFile($viewContext, $gridName);
        $this->configurationService->setInCaches($grid, $form && $form->getOption(Form::OPTION_STATIC), $cacheKey);
        return $grid;
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
        unset($row);
        if (0 === strpos($this->templatePathAndFilename, 'EXT:') || 0 !== strpos($this->templatePathAndFilename, '/')) {
            $path = GeneralUtility::getFileAbsFileName($this->templatePathAndFilename);
            if (true === empty($path)) {
                return null;
            }
            return $path;
        }
        return $this->templatePathAndFilename;
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
        return $this->configurationService->convertFlexFormContentToArray(
            $row[$fieldName],
            $form,
            'lDEF',
            'vDEF'
        );
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
        $record = $GLOBALS['TSFE']->page;
        if ($GLOBALS['TSFE']->sys_language_uid != 0) {
            $localisation = $this->recordService->get(
                'pages_language_overlay',
                '*',
                'pid = "' . $record['uid'] .
                '" AND sys_language_uid = "' . $GLOBALS['TSFE']->sys_language_uid . '"' .
                ' AND hidden = false' .
                ' AND deleted = false' .
                ' AND (starttime = 0 OR starttime <= UNIX_TIMESTAMP())' .
                ' AND (endtime = 0 OR endtime > UNIX_TIMESTAMP())'
            );
        }
        if (false === empty($localisation)) {
            $mergedRecord = RecursiveArrayUtility::merge($record, reset($localisation));
            if (isset($record['uid']) && isset($record['pid'])) {
                $mergedRecord['uid'] = $record['uid'];
                $mergedRecord['pid'] = $record['pid'];
            }
            return $mergedRecord;
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
        $variables['user'] = $GLOBALS['TSFE']->fe_user->user;
        if (true === file_exists($this->getTemplatePathAndFilename($row))) {
            $variables['grid'] = $this->getGrid($row);
            $variables['form'] = $this->getForm($row);
        }
        return $variables;
    }

    /**
     * @param array $row
     * @return array
     */
    public function getTemplatePaths(array $row)
    {
        GeneralUtility::logDeprecatedFunction();
        $paths = $this->templatePaths;
        if (false === is_array($paths)) {
            $extensionKey = $this->getExtensionKey($row);
            $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
            if (false === empty($extensionKey)) {
                $paths = $this->configurationService->getViewConfigurationForExtensionName($extensionKey);
            }
        }
        if (true === is_array($paths)) {
            $paths = PathUtility::translatePath($paths);
        }
        return $paths;
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
        if ('update' === $operation || 'new' === $operation) {
            $record = $reference->datamap[$this->tableName][$id];
            $stored = $this->recordService->getSingle($this->tableName, '*', $record['uid']);
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
            $this->recordService->update($this->tableName, $stored);
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
        // We dispatch the Outlet associated with the Form, triggering each defined
        // Pipe inside the Outlet to "conduct" the data.
        $record = $this->loadRecordFromDatabase($id);
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
        $form = $this->getForm($row);
        if (null !== $form) {
            $dataStructure = $form->build();
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
    }

    /**
     * @return PreviewView
     */
    protected function getPreviewView()
    {
        GeneralUtility::logDeprecatedFunction();
        $preview = 'FluidTYPO3\\Flux\\View\\PreviewView';
        return GeneralUtility::makeInstance(ObjectManager::class)->get($preview);
    }

    /**
     * Get preview chunks - header and content - as
     * [string $headerContent, string $previewContent, boolean $continueRendering)
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
        $previewContent = $this->getPreviewView()->getPreview($this, $row);
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
        $base = array_pop(explode($separator, $class));
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

    /**
     * @param integer $uid
     * @return array|NULL
     */
    protected function loadRecordFromDatabase($uid)
    {
        $uid = intval($uid);
        $tableName = $this->tableName;
        return $this->recordService->getSingle($tableName, '*', $uid);
    }

}
