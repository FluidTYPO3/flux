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

/**
 * AbstractProvider
 */
class AbstractProvider implements ProviderInterface {

	const FORM_CLASS_PATTERN = '%s\\Form\\%s\\%sForm';

	const CONTENT_OBJECT_TYPE_LIST = 'list';

	/**
	 * @var array
	 */
	private static $cache = array();

	/**
	 * @var array
	 */
	private static $trackedMethodCalls = array();

	/**
	 * @var string
	 */
	protected $name = NULL;

	/**
	 * Fill with the table column name which should trigger this Provider.
	 *
	 * @var string
	 */
	protected $fieldName = NULL;

	/**
	 * Fill with the name of the DB table which should trigger this Provider.
	 *
	 * @var string
	 */
	protected $tableName = NULL;

	/**
	 * Fill with the "list_type" value that should trigger this Provider.
	 *
	 * @var string
	 */
	protected $listType = NULL;

	/**
	 * Fill with the "CType" value that should trigger this Provider.
	 *
	 * @var string
	 */
	protected $contentObjectType = NULL;

	/**
	 * @var string
	 */
	protected $parentFieldName = NULL;

	/**
	 * @var array|NULL
	 */
	protected $row = NULL;

	/**
	 * @var array
	 */
	protected $dataStructArray;

	/**
	 * @var string|NULL
	 */
	protected $templatePathAndFilename = NULL;

	/**
	 * @var array
	 */
	protected $templateVariables = array();

	/**
	 * @var array|NULL
	 */
	protected $templatePaths = NULL;

	/**
	 * @var string|NULL
	 */
	protected $configurationSectionName = 'Configuration';

	/**
	 * @var string|NULL
	 */
	protected $extensionKey = NULL;

	/**
	 * @var integer
	 */
	protected $priority = 50;

	/**
	 * @var Form
	 */
	protected $form = NULL;

	/**
	 * @var Grid
	 */
	protected $grid = NULL;

	/**
	 * @var ViewContext
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
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param WorkspacesAwareRecordService $recordService
	 * @return void
	 */
	public function injectRecordService(WorkspacesAwareRecordService $recordService) {
		$this->recordService = $recordService;
	}

	/**
	 * @param array $settings
	 * @return void
	 */
	public function loadSettings(array $settings) {
		if (TRUE === isset($settings['name'])) {
			$this->setName($settings['name']);
		}
		if (TRUE === isset($settings['form'])) {
			$form = Form::create($settings['form']);
			if (TRUE === isset($settings['extensionKey'])) {
				$extensionKey = $settings['extensionKey'];
				$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
				$form->setExtensionName($extensionName);
			}
			$settings['form'] = $form;
		}
		if (TRUE === isset($settings['grid'])) {
			$settings['grid'] = Grid::create($settings['grid']);
		}
		foreach ($settings as $name => $value) {
			$this->$name = $value;
		}
		$fieldName = $this->getFieldName(array());
		if (TRUE === isset($settings['listType'])) {
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
	public function trigger(array $row, $table, $field, $extensionKey = NULL) {
		$providerFieldName = $this->getFieldName($row);
		$providerTableName = $this->tableName;
		$providerExtensionKey = $this->extensionKey;
		$contentObjectType = $this->contentObjectType;
		$listType = $this->listType;
		$rowContainsPlugin = (FALSE === empty($row['CType']) && self::CONTENT_OBJECT_TYPE_LIST === $row['CType']);
		$rowIsEmpty = (0 === count($row));
		$matchesContentType = ((TRUE === empty($contentObjectType) && TRUE === empty($row['CType'])) || (FALSE === empty($row['CType']) && $row['CType'] === $contentObjectType));
		$matchesPluginType = ((FALSE === empty($row['list_type']) && $row['list_type'] === $listType));
		$matchesTableName = ($providerTableName === $table || NULL === $table);
		$matchesFieldName = ($providerFieldName === $field || NULL === $field);
		$matchesExtensionKey = ($providerExtensionKey === $extensionKey || NULL === $extensionKey);
		$isFullMatch = $matchesExtensionKey && $matchesTableName && $matchesFieldName && ($matchesContentType || ($rowContainsPlugin && $matchesPluginType));
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
	protected function resolveFormClassName(array $row) {
		$packageName = $this->getControllerPackageNameFromRecord($row);
		$packageKey = str_replace('.', '\\', $packageName);
		$controllerName = $this->getControllerNameFromRecord($row);
		$action = $this->getControllerActionFromRecord($row);
		$expectedClassName = sprintf(self::FORM_CLASS_PATTERN, $packageKey, $controllerName, ucfirst($action));
		return TRUE === class_exists($expectedClassName) ? $expectedClassName : NULL;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	protected function getViewVariables(array $row) {
		$extensionKey = $this->getExtensionKey($row);
		$fieldName = $this->getFieldName($row);
		$variables = array(
			'record' => $row,
			'settings' => $this->configurationService->getSettingsForExtensionName($extensionKey)
		);

		// Special case: when saving a new record variable $row[$fieldName] is already an array
		// and must not be processed by the configuration service. This has limited support from
		// Flux (essentially: no Form instance which means no inheritance, transformation or
		// form options can be dependended upon at this stage).
		$lang = $this->getCurrentLanguageName();
		$value = $this->getCurrentValuePointerName();
		if (FALSE === is_array($row[$fieldName])) {
			$recordVariables = $this->configurationService->convertFlexFormContentToArray($row[$fieldName], NULL, $lang, $value);
			$variables = RecursiveArrayUtility::mergeRecursiveOverrule($variables, $recordVariables);
		}

		$variables = RecursiveArrayUtility::mergeRecursiveOverrule($this->templateVariables, $variables);

		return $variables;
	}

	/**
	 * @param array $row
	 * @return ViewContext
	 */
	public function getViewContext(array $row, RequestInterface $request = NULL) {
		if (FALSE === $this->viewContext instanceof ViewContext) {
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
	 * @return void
	 */
	public function setViewContext(ViewContext $viewContext) {
		$this->viewContext = $viewContext;
	}

	/**
	 * @param array $row
	 * @return Form|NULL
	 */
	public function getForm(array $row) {
		if (NULL !== $this->form) {
			return $this->form;
		}
		$formName = 'form';
		$cacheKey = $this->getCacheKeyForStoredVariable($row, $formName);
		if (FALSE === array_key_exists($cacheKey, self::$cache)) {
			$formClassName = $this->resolveFormClassName($row);
			if (NULL !== $formClassName) {
				$form = call_user_func_array(array($formClassName, 'create'), array($row));
			} else {
				$viewContext = $this->getViewContext($row);
				if (NULL !== $viewContext->getTemplatePathAndFilename()) {
					$view = $this->configurationService->getPreparedExposedTemplateView($viewContext);
					$form = $view->getForm($viewContext->getSectionName(), $formName);
				}
			}
			if (NULL !== $form) {
				$form->setOption(Form::OPTION_RECORD, $row);
				$form->setOption(Form::OPTION_RECORD_TABLE, $this->getTableName($row));
				$form->setOption(Form::OPTION_RECORD_FIELD, $this->getFieldName($row));
			}
			self::$cache[$cacheKey] = $form;
		}
		return self::$cache[$cacheKey];
	}

	/**
	 * @param array $row
	 * @return Grid
	 */
	public function getGrid(array $row) {
		if (NULL !== $this->grid) {
			return $this->grid;
		}
		$gridName = 'grid';
		$cacheKey = $this->getCacheKeyForStoredVariable($row, $gridName);
		if (FALSE === array_key_exists($cacheKey, self::$cache)) {
			$viewContext = $this->getViewContext($row);
			$grid = $this->configurationService->getGridFromTemplateFile($viewContext, $gridName);
			self::$cache[$cacheKey] = $grid;
		}
		return self::$cache[$cacheKey];
	}

	/**
	 * @param string $listType
	 */
	public function setListType($listType) {
		$this->listType = $listType;
	}

	/**
	 * @return string
	 */
	public function getListType() {
		return $this->listType;
	}

	/**
	 * @param string $contentObjectType
	 */
	public function setContentObjectType($contentObjectType) {
		$this->contentObjectType = $contentObjectType;
	}

	/**
	 * @return string
	 */
	public function getContentObjectType() {
		return $this->contentObjectType;
	}

	/**
	 * @param array $row The record row which triggered processing
	 * @return string|NULL
	 */
	public function getFieldName(array $row) {
		return $this->fieldName;
	}

	/**
	 * @param array $row
	 * @return string
	 */
	public function getParentFieldName(array $row) {
		unset($row);
		return $this->parentFieldName;
	}

	/**
	 * @param array $row The record row which triggered processing
	 * @return string|NULL
	 */
	public function getTableName(array $row) {
		unset($row);
		return $this->tableName;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getTemplatePathAndFilename(array $row) {
		unset($row);
		if (0 === strpos($this->templatePathAndFilename, 'EXT:') || 0 !== strpos($this->templatePathAndFilename, '/')) {
			$path = GeneralUtility::getFileAbsFileName($this->templatePathAndFilename);
			if (TRUE === empty($path)) {
				return NULL;
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
	public function getFlexFormValues(array $row) {
		$fieldName = $this->getFieldName($row);
		$form = $this->getForm($row);
		$languageName = $this->getCurrentLanguageName();
		$valuePointer = $this->getCurrentValuePointerName();
		return $this->configurationService->convertFlexFormContentToArray($row[$fieldName], $form, $languageName, $valuePointer);
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
	protected function getCurrentLanguageName() {
		$language = $GLOBALS['TSFE']->lang;
		if (TRUE === empty($language) || 'default' === $language) {
			$language = NULL;
		}
		return $language;
	}

	/**
	 * Gets the pointer name to use whne retrieving values from a
	 * flexform source. Return NULL when pointer is default.
	 *
	 * @return string|NULL
	 */
	protected function getCurrentValuePointerName() {
		return $this->getCurrentLanguageName();
	}

	/**
	 * Returns the page record with localisation applied, if any
	 * exists in database. Maintains uid and pid of the original
	 * page if localisation is applied.
	 *
	 * @return array
	 */
	protected function getPageValues() {
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
		if (FALSE === empty($localisation)) {
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
	public function getTemplateVariables(array $row) {
		$variables = (array) $this->templateVariables;
		$variables['record'] = $row;
		$variables['page'] = $this->getPageValues();
		$variables['user'] = $GLOBALS['TSFE']->fe_user->user;
		if (TRUE === file_exists($this->getTemplatePathAndFilename($row))) {
			$variables['grid'] = $this->getGrid($row);
			$variables['form'] = $this->getForm($row);
		}
		return $variables;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	public function getTemplatePaths(array $row) {
		$paths = $this->templatePaths;
		if (FALSE === is_array($paths)) {
			$extensionKey = $this->getExtensionKey($row);
			$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
			if (FALSE === empty($extensionKey)) {
				$paths = $this->configurationService->getViewConfigurationForExtensionName($extensionKey);
			}
		}
		if (TRUE === is_array($paths)) {
			$paths = PathUtility::translatePath($paths);
		}
		return $paths;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getConfigurationSectionName(array $row) {
		unset($row);
		return $this->configurationSectionName;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getExtensionKey(array $row) {
		unset($row);
		return $this->extensionKey;
	}

	/**
	 * @param array $row
	 * @return integer
	 */
	public function getPriority(array $row) {
		unset($row);
		return $this->priority;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Pre-process record data for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @param array $row The record data, by reference. Changing fields' values changes the record's values before display
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param DataHandler $reference A reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object that is currently displaying the record
	 * @return void
	 */
	public function preProcessRecord(array &$row, $id, DataHandler $reference) {
		$fieldName = $this->getFieldName($row);
		$tableName = $this->getTableName($row);
		if (TRUE === is_array($row[$fieldName]) && TRUE === isset($row[$fieldName]['data']['options']['lDEF']) && TRUE === is_array($row[$fieldName]['data']['options']['lDEF'])) {
			foreach ($row[$fieldName]['data']['options']['lDEF'] as $key => $value) {
				if (0 === strpos($key, $tableName)) {
					$realKey = array_pop(explode('.', $key));
					if (TRUE === isset($row[$realKey])) {
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
	 * @param array $row the record data, by reference. Changing fields' values changes the record's values just before saving
	 * @param DataHandler $reference A reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object that is currently saving the record
	 * @param array $removals Allows overridden methods to pass an additional array of field names to remove from the stored Flux value
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, DataHandler $reference, array $removals = array()) {
		if ('update' === $operation) {
			$record = $reference->datamap[$this->tableName][$id];
			$stored = $this->recordService->getSingle($this->tableName, '*', $record['uid']);
			$fieldName = $this->getFieldName((array) $record);
			$dontProcess = (
				NULL === $fieldName
				|| FALSE === isset($row[$fieldName])
				|| FALSE === isset($record[$fieldName]['data'])
				|| FALSE === is_array($record[$fieldName]['data'])
			);
			if (TRUE === $dontProcess) {
				return;
			}
			$data = $record[$fieldName]['data'];
			foreach ($data as $sheetName => $sheetFields) {
				foreach ($sheetFields['lDEF'] as $sheetFieldName => $fieldDefinition) {
					if ('_clear' === substr($sheetFieldName, -6)) {
						array_push($removals, $sheetFieldName);
					} else {
						$clearFieldName = $sheetFieldName . '_clear';
						$clearFieldValue = (boolean) (TRUE === isset($data[$sheetName]['lDEF'][$clearFieldName]['vDEF']) ? $data[$sheetName]['lDEF'][$clearFieldName]['vDEF'] : 0);
						$shouldClearField = (0 < $data[$sheetName]['lDEF'][$clearFieldName]['vDEF']);
						if (TRUE === $shouldClearField || TRUE === $clearFieldValue) {
							array_push($removals, $sheetFieldName);
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
	 * @param array $row The record's data, by reference. Changing fields' values changes the record's values just before saving after operation
	 * @param DataHandler $reference A reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object that is currently performing the database operation
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, DataHandler $reference) {
		// We dispatch the Outlet associated with the Form, triggering each defined
		// Pipe inside the Outlet to "conduct" the data.
		$record = $this->loadRecordFromDatabase($id);
		if (NULL !== $record) {
			$form = $this->getForm($record);
			if (TRUE === $form instanceof Form\FormInterface) {
				$form->getOutlet()->fill(array(
					'command' => $status,
					'uid' => $id,
					'record' => $row,
					'table' => $this->getTableName($record),
					'provider' => $this,
					'dataHandler' => $reference
				));
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
	public function preProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference) {
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
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference) {
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
	public function postProcessDataStructure(array &$row, &$dataStructure, array $conf) {
		$form = $this->getForm($row);
		if (NULL !== $form) {
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
	 * @return array The large FormEngine configuration array - see FormEngine documentation!
	 */
	public function processTableConfiguration(array $row, array $configuration) {
		return $configuration;
	}

	/**
	 * Perform various cleanup operations upon clearing cache
	 *
	 * @param array $command
	 * @return void
	 */
	public function clearCacheCommand($command = array()) {
	}

	/**
	 * @return PreviewView
	 */
	protected function getPreviewView() {
		$preview = 'FluidTYPO3\\Flux\\View\\PreviewView';
		return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get($preview);
	}

	/**
	 * Get preview chunks - header and content - as
	 * array(string $headerContent, string $previewContent, boolean $continueRendering)
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
	public function getPreview(array $row) {
		$previewContent = $this->getPreviewView()->getPreview($this, $row);
		return array(NULL, $previewContent, empty($previewContent));
	}

	/**
	 * @param array $row
	 * @param string $variable
	 * @return string
	 */
	protected function getCacheKeyForStoredVariable(array $row, $variable) {
		$table = $this->getTableName($row);
		$field = $this->getFieldName($row);
		$uid = (TRUE === isset($row['uid']) ? $row['uid'] : uniqid());
		return $table . $this->getListType() . $this->getContentObjectType() . md5(serialize($row[$field])) . $uid . $variable . get_class($this);
	}

	/**
	 * Stub: override this to return a controller action name associated with $row.
	 * Default strategy: return base name of Provider class minus the "Provider" suffix.
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerNameFromRecord(array $row) {
		$class = get_class($this);
		$separator = FALSE !== strpos($class, '\\') ? '\\' : '_';
		$base = array_pop(explode($separator, $class));
		return substr($base, 0, -8);
	}

	/**
	 * Stub: Get the extension key of the controller associated with $row
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerExtensionKeyFromRecord(array $row) {
		return $this->extensionKey;
	}

	/**
	 * Stub: Get the package name of the controller associated with $row
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerPackageNameFromRecord(array $row) {
		$extensionKey = $this->getControllerExtensionKeyFromRecord($row);
		$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
		$vendor = ExtensionNamingUtility::getVendorName($extensionKey);
		return NULL !== $vendor ? $vendor . '.' . $extensionName : $extensionName;
	}

	/**
	 * Stub: Get the name of the controller action associated with $row
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionFromRecord(array $row) {
		return 'default';
	}

	/**
	 * Stub: Get a compacted controller name + action name string
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionReferenceFromRecord(array $row) {
		return $this->getControllerNameFromRecord($row) . '->' . $this->getControllerActionFromRecord($row);
	}

	/**
	 * @param string $tableName
	 * @return void
	 */
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}

	/**
	 * @param string $fieldName
	 * @return void
	 */
	public function setFieldName($fieldName) {
		$this->fieldName = $fieldName;
	}

	/**
	 * @param string $extensionKey
	 * @return void
	 */
	public function setExtensionKey($extensionKey) {
		$this->extensionKey = $extensionKey;
	}

	/**
	 * @param array|NULL $templateVariables
	 * @return void
	 */
	public function setTemplateVariables($templateVariables) {
		$this->templateVariables = $templateVariables;
	}

	/**
	 * @param string $templatePathAndFilename
	 * @return void
	 */
	public function setTemplatePathAndFilename($templatePathAndFilename) {
		$this->templatePathAndFilename = $templatePathAndFilename;
	}

	/**
	 * @param array|NULL $templatePaths
	 * @return void
	 */
	public function setTemplatePaths($templatePaths) {
		$this->templatePaths = $templatePaths;
	}

	/**
	 * @param string|NULL $configurationSectionName
	 * @return void
	 */
	public function setConfigurationSectionName($configurationSectionName) {
		$this->configurationSectionName = $configurationSectionName;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param Form $form
	 */
	public function setForm(Form $form) {
		$this->form = $form;
	}

	/**
	 * @param Grid $grid
	 */
	public function setGrid(Grid $grid) {
		$this->grid = $grid;
	}

	/**
	 * @param integer $uid
	 * @return array|NULL
	 */
	protected function loadRecordFromDatabase($uid) {
		$uid = intval($uid);
		$tableName = $this->tableName;
		return $this->recordService->getSingle($tableName, '*', $uid);
	}

	/**
	 * Use by TceMain to track method calls to providers for a certain $id.
	 * Every provider should only be called once per method / $id / command.
	 * When TceMain has called the provider it will call this method afterwards.
	 *
	 * @param string $methodName
	 * @param mixed $id
	 * @param string command
	 * @return void
	 */
	public function trackMethodCall($methodName, $id, $command = '') {
		self::trackMethodCallWithClassName(get_called_class(), $methodName, $id, $command);
	}

	/**
	 * Use by TceMain to track method calls to providers for a certain $id.
	 * Every provider should only be called once per method / $id.
	 * Before calling a provider, TceMain will call this method.
	 * If the provider hasn't been called for that method / $id before, it is.
	 *
	 *
	 * @param string $methodName
	 * @param mixed $id
	 * @param string $command
	 * @return boolean
	 */
	public function shouldCall($methodName, $id, $command = '') {
		return self::shouldCallWithClassName(get_class($this), $methodName, $id, $command);
	}

	/**
	 * Internal method. See trackMethodCall.
	 * This is used by flux own provider to make sure on inheritance they are still only executed once.
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param mixed $id
	 * @param string $command
	 * @return void
	 */
	protected function trackMethodCallWithClassName($className, $methodName, $id, $command = '') {
		$cacheKey = $className . $methodName . $id . $command;
		self::$trackedMethodCalls[$cacheKey] = TRUE;
	}

	/**
	 * Internal method. See shouldCall.
	 * This is used by flux own provider to make sure on inheritance they are still only executed once.
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param mixed $id
	 * @param string $command
	 * @return boolean
	 */
	protected function shouldCallWithClassName($className, $methodName, $id, $command = '') {
		$cacheKey = $className . $methodName . $id . $command;
		return empty(self::$trackedMethodCalls[$cacheKey]);
	}

	/**
	 * @return void
	 */
	public function reset() {
		self::$cache = array();
		self::$trackedMethodCalls = array();
	}

}
