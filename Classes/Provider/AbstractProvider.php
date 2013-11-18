<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

/**
 * @package Flux
 * @subpackage Provider
 */
class Tx_Flux_Provider_AbstractProvider implements Tx_Flux_Provider_ProviderInterface {

	/**
	 * @var array
	 */
	private static $cache = array();

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
	 * @var Tx_Flux_Form
	 */
	protected $form = NULL;

	/**
	 * @var Tx_Flux_Grid
	 */
	protected $grid = NULL;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Flux_Service_ContentService
	 */
	protected $contentService;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param Tx_Flux_Service_ContentService $contentService
	 * @return void
	 */
	public function injectContentService(Tx_Flux_Service_ContentService $contentService) {
		$this->contentService = $contentService;
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
			$form = Tx_Flux_Form::create($settings['form']);
			if (TRUE === isset($settings['extensionKey'])) {
				$extensionKey = $settings['extensionKey'];
				$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extensionKey);
				$form->setExtensionName($extensionName);
			}
			$settings['form'] = $form;
		}
		if (TRUE === isset($settings['grid'])) {
			$settings['grid'] = Tx_Flux_Form_Container_Grid::create($settings['grid']);
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
		$rowIsEmpty = (0 === count($row));
		$matchesContentType = ((TRUE === empty($contentObjectType) && TRUE === empty($row['CType'])) || (FALSE === empty($row['CType']) && $row['CType'] === $contentObjectType));
		$matchesPluginType = ((TRUE === empty($listType) && TRUE === empty($row['list_type'])) || (FALSE === empty($row['list_type']) && $row['list_type'] === $listType));
		$matchesTableName = ($providerTableName === $table || NULL === $table);
		$matchesFieldName = ($providerFieldName === $field || NULL === $field);
		$matchesExtensionKey = ($providerExtensionKey === $extensionKey || NULL === $extensionKey);
		$isFullMatch = (($matchesExtensionKey && $matchesTableName && $matchesFieldName) && ($matchesContentType || $matchesPluginType));
		$isFallbackMatch = ($matchesTableName && $matchesFieldName && $rowIsEmpty);
		return ($isFullMatch || $isFallbackMatch);
	}

	/**
	 * @param array $row
	 * @return Tx_Flux_Form|NULL
	 */
	public function getForm(array $row) {
		if (NULL !== $this->form) {
			return $this->form;
		}
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		if (FALSE === file_exists($templatePathAndFilename)) {
			return NULL;
		}
		$section = $this->getConfigurationSectionName($row);
		$formName = 'form';
		$paths = $this->getTemplatePaths($row);
		$extensionKey = $this->getExtensionKey($row);
		$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extensionKey);
		$fieldName = $this->getFieldName($row);
		$variables = $this->configurationService->convertFlexFormContentToArray($row[$fieldName]);
		$form = $this->configurationService->getFormFromTemplateFile($templatePathAndFilename, $section, $formName, $paths, $extensionName, $variables);
		$form = $this->setDefaultValuesInFieldsWithInheritedValues($form, $row);
		return $form;
	}

	/**
	 * @param array $row
	 * @return Tx_Flux_Form_Container_Grid
	 */
	public function getGrid(array $row) {
		if (NULL !== $this->grid) {
			return $this->grid;
		}
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		$section = $this->getConfigurationSectionName($row);
		$gridName = 'grid';
		$paths = $this->getTemplatePaths($row);
		$extensionKey = $this->getExtensionKey($row);
		$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extensionKey);
		$fieldName = $this->getFieldName($row);
		$variables = $this->configurationService->convertFlexFormContentToArray($row[$fieldName]);
		$grid = $this->configurationService->getGridFromTemplateFile($templatePathAndFilename, $section, $gridName, $paths, $extensionName, $variables);
		return $grid;
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
			return \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->templatePathAndFilename);
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
		$cacheKey = 'values_' . md5(json_encode($row));
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$fieldName = $this->getFieldName($row);
		$form = $this->getForm($row);
		$immediateConfiguration = $this->configurationService->convertFlexFormContentToArray($row[$fieldName], $form, NULL, NULL);
		$tree = $this->getInheritanceTree($row);
		if (0 === count($tree)) {
			self::$cache[$cacheKey] = $immediateConfiguration;
			return (array) $immediateConfiguration;
		}
		$inheritedConfiguration = $this->getMergedConfiguration($tree);
		if (0 === count($immediateConfiguration)) {
			self::$cache[$cacheKey] = $inheritedConfiguration;
			return (array) $inheritedConfiguration;
		}
		$merged = Tx_Flux_Utility_RecursiveArray::merge($inheritedConfiguration, $immediateConfiguration);
		self::$cache[$cacheKey] = $merged;
		return $merged;
	}

	/**
	 * @param array $row
	 * @return array|NULL
	 */
	public function getTemplateVariables(array $row) {
		$variables = (array) $this->templateVariables;
		$variables['record'] = $row;
		$variables['page'] = $GLOBALS['TSFE']->page;
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
		$extensionKey = $this->getExtensionKey($row);
		if (FALSE !== strpos($extensionKey, '.')) {
			$extensionKey = strtolower(array_pop(explode('.', $extensionKey)));
		}
		if (FALSE === is_array($paths)) {
			$extensionKey = $this->getExtensionKey($row);
			if (FALSE === empty($extensionKey) && TRUE === \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extensionKey)) {
				$paths = $this->configurationService->getViewConfigurationForExtensionName($extensionKey);
			}
		}

		if (NULL !== $paths && FALSE === is_array($paths)) {
			$this->configurationService->message('Template paths resolved for "' . $extensionKey . '" was not an array.', \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_WARNING);
			$paths = NULL;
		}

		if (NULL === $paths) {
			$extensionKey = $this->getExtensionKey($row);
			if (FALSE === empty($extensionKey) && TRUE === \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extensionKey)) {
				$paths = array(
					\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey, 'Resources/Private/Templates/'),
					\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey, 'Resources/Private/Partials/'),
					\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey, 'Resources/Private/Layouts/')
				);
			} else {
				$paths = array();
			}
		}

		if (TRUE === is_array($paths)) {
			$paths = Tx_Flux_Utility_Path::translatePath($paths);
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
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference A reference to the t3lib_TCEmain object that is currently displaying the record
	 * @return void
	 */
	public function preProcessRecord(array &$row, $id, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
		$fieldName = $this->getFieldName($row);
		$tableName = $this->getTableName($row);
		if (TRUE === is_array($row[$fieldName]['data']) && TRUE === is_array($row[$fieldName]['data']['options']['lDEF'])) {
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
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference A reference to the t3lib_TCEmain object that is currently saving the record
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
		if ('update' === $operation) {
			$fieldName = $this->getFieldName((array) $reference->datamap[$this->tableName][$id]);
			if (NULL === $fieldName) {
				return;
			}
			if (FALSE === isset($row[$fieldName])) {
				return;
			}
			$data = $reference->datamap[$this->tableName][$id][$fieldName]['data'];
			if (FALSE === is_array($data)) {
				return;
			}
			$removals = array();
			foreach ($data as $sheetName => $sheetFields) {
				foreach ($sheetFields['lDEF'] as $sheetFieldName => $fieldDefinition) {
					if ('_clear' === substr($sheetFieldName, -6)) {
						array_push($removals, $sheetFieldName);
					} else {
						$clearFieldName = $sheetFieldName . '_clear';
						$clearFieldValue = TRUE === isset($data[$sheetName]['lDEF'][$clearFieldName]['vDEF']) ? $data[$sheetName]['lDEF'][$clearFieldName]['vDEF'] : 0;
						$inheritedValue = $this->getInheritedPropertyValueByDottedPath($row, $sheetFieldName);
						$shouldClearField = (0 < $data[$sheetName]['lDEF'][$clearFieldName]['vDEF'] || (NULL !== $inheritedValue && $inheritedValue == $fieldDefinition['vDEF']));
						if (TRUE === $shouldClearField) {
							array_push($removals, $sheetFieldName);
						}
					}
				}
			}
			$dom = new DOMDocument();
			$dom->loadXML($row[$fieldName]);
			$dom->preserveWhiteSpace = FALSE;
			$dom->formatOutput = TRUE;
			foreach ($dom->getElementsByTagName('field') as $fieldNode) {
				if (TRUE === in_array($fieldNode->getAttribute('index'), $removals)) {
					$fieldNode->parentNode->removeChild($fieldNode);
				}
			}
			$row[$fieldName] = $dom->saveXML();
		}
	}

	/**
	 * Post-process database operation for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $status TYPO3 operation identifier, i.e. "new" etc.
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param array $row The record's data, by reference. Changing fields' values changes the record's values just before saving after operation
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference A reference to the t3lib_TCEmain object that is currently performing the database operation
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
		unset($status, $id, $row, $reference);
	}

	/**
	 * Pre-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 * @return void
	 */
	public function preProcessCommand($command, $id, array &$row, &$relativeTo, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
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
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
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
	 * Perform various cleanup operations upon clearing cache
	 *
	 * @param array $command
	 * @return void
	 */
	public function clearCacheCommand($command = array()) {
		if (TRUE === isset($command['uid'])) {
			return;
		}
		$files = glob(PATH_site . 'typo3temp/flux-*');
		if (TRUE === is_array($files)) {
			foreach ($files as $fileName) {
				unlink($fileName);
			}
		}
	}

	/**
	 * Gets an inheritance tree (ordered parent -> ... -> this record)
	 * of record arrays containing raw values.
	 *
	 * @param array $row
	 * @return array
	 */
	public function getInheritanceTree(array $row) {
		$cacheKey = 'tree_' . $row['uid'];
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		self::$cache[$cacheKey] = array();
		if (NULL !== $this->getFieldName($row)) {
			self::$cache[$cacheKey] = $this->loadRecordTreeFromDatabase($row);
		}
		return self::$cache[$cacheKey];
	}

	/**
	 * Get preview chunks - header and content - as array($header, $content)
	 *
	 * @param array $row The record data to be analysed for variables to use in a rendered preview
	 * @return array
	 */
	public function getPreview(array $row) {
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		if (FALSE === file_exists($templatePathAndFilename)) {
			return array(NULL, NULL);
		}
		$extensionKey = $this->getExtensionKey($row);
		$flexformVariables = $this->getFlexFormValues($row);
		$templateVariables = $this->getTemplateVariables($row);
		$variables = Tx_Flux_Utility_RecursiveArray::merge($templateVariables, $flexformVariables);
		$paths = $this->getTemplatePaths($row);
		$form = $this->getForm($row);
		$formLabel = $form->getLabel();
		$label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($formLabel, $extensionKey);
		$variables['label'] = $label;
		$variables['row'] = $row;

		$view = $this->configurationService->getPreparedExposedTemplateView($extensionKey, 'Content', $paths, $variables);
		$view->setTemplatePathAndFilename($templatePathAndFilename);

		$existingContentObject = $this->configurationManager->getContentObject();
		$contentObject = new tslib_cObj();
		$contentObject->start($row, $this->getTableName($row));
		$this->configurationManager->setContentObject($contentObject);
		$previewContent = $view->renderStandaloneSection('Preview', $variables);
		$this->configurationManager->setContentObject($existingContentObject);
		$previewContent = trim($previewContent);
		$headerContent = '';
		if (FALSE === empty($label) || FALSE === empty($row['header'])) {
			$headerContent = '<div><strong>' . $label . '</strong> <i>' . $row['header'] . '</i></div>';
		}
		return array($headerContent, $previewContent);
	}

	/**
	 * @param Tx_Flux_Form $form
	 * @param array $row
	 * @return Tx_Flux_Form
	 */
	protected function setDefaultValuesInFieldsWithInheritedValues(Tx_Flux_Form $form, array $row) {
		foreach ($form->getFields() as $field) {
			$name = $field->getName();
			$inheritedValue = $this->getInheritedPropertyValueByDottedPath($row, $name);
			if (NULL !== $inheritedValue && TRUE === $field instanceof Tx_Flux_Form_FieldInterface) {
				$field->setDefault($inheritedValue);
			}
		}
		return $form;
	}

	/**
	 * @param array $row
	 * @param string $propertyPath
	 * @return mixed
	 */
	protected function getInheritedPropertyValueByDottedPath(array $row, $propertyPath) {
		$tree = $this->getInheritanceTree($row);
		$inheritedConfiguration = $this->getMergedConfiguration($tree);
		if (FALSE === strpos($propertyPath, '.')) {
			return TRUE === isset($inheritedConfiguration[$propertyPath]) ? \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($inheritedConfiguration, $propertyPath) : NULL;
		}
		return \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($inheritedConfiguration, $propertyPath);
	}

	/**
	 * @param array $tree
	 * @param string $cacheKey Overrides the cache key
	 * @param boolean $mergeToCache Merges the configuration of $tree to the current $cacheKey
	 * @return array
	 */
	protected function getMergedConfiguration(array $tree, $cacheKey = NULL, $mergeToCache = FALSE) {
		if (NULL === $cacheKey) {
			$cacheKey = $this->getCacheKeyForMergedConfiguration($tree);
		}
		if (FALSE === $mergeToCache && TRUE === $this->hasCacheForMergedConfiguration($cacheKey)) {
			return self::$cache[$cacheKey];
		}
		$data = array();
		foreach ($tree as $branch) {
			$form = $this->getForm($branch);
			if (NULL === $form) {
				self::$cache[$cacheKey] = $data;
				return $data;
			}
			$fields = $form->getFields();
			$values = $this->getFlexFormValues($branch);
			foreach ($fields as $field) {
				$name = $field->getName();
				if (FALSE === $field instanceof Tx_Flux_Form_FieldInterface) {
					continue;
				}
				$stop = (TRUE === $field->getStopInheritance());
				$inherit = (TRUE === $field->getInheritEmpty());
				$empty = (TRUE === empty($values[$name]) && $values[$name] !== '0' && $values[$name] !== 0);
				if (TRUE === $stop) {
					unset($values[$name]);
				} elseif (FALSE === $inherit && TRUE === $empty) {
					unset($values[$name]);
				}
			}
			$data = Tx_Flux_Utility_RecursiveArray::merge($data, $values);
		}
		if (TRUE === $mergeToCache && TRUE === $this->hasCacheForMergedConfiguration($cacheKey)) {
			$data = Tx_Flux_Utility_RecursiveArray::merge(self::$cache[$cacheKey], $data);
		}
		self::$cache[$cacheKey] = $data;
		return $data;
	}

	/**
	 * @param array $tree
	 * @return string
	 */
	protected function getCacheKeyForMergedConfiguration(array $tree) {
		return 'merged_' . md5(json_encode($tree));
	}

	/**
	 * @param string $cacheKey
	 * @return boolean
	 */
	protected function hasCacheForMergedConfiguration($cacheKey) {
		return TRUE === isset(self::$cache[$cacheKey]);
	}

	/**
	 * @param array $row
	 * @return mixed
	 */
	protected function getParentFieldValue(array $row) {
		$parentFieldName = $this->getParentFieldName($row);
		$tableName = $this->getTableName($row);
		if (NULL !== $parentFieldName && FALSE === isset($row[$parentFieldName])) {
			$row = $this->loadRecordFromDatabase($row['uid']);
		}
		return $row[$parentFieldName];
	}

	/**
	 * Stub: Override this when ConfigurationProvider is associated with a Controller
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerExtensionKeyFromRecord(array $row) {
		return NULL;
	}

	/**
	 * Stub: Override this when ConfigurationProvider is associated with a Controller
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionFromRecord(array $row) {
		return NULL;
	}

	/**
	 * Stub: implement this in Controllers which store the action in a record field.
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionReferenceFromRecord(array $row) {
		return NULL;
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
	 * @param Tx_Flux_Form $form
	 */
	public function setForm(Tx_Flux_Form $form) {
		$this->form = $form;
	}

	/**
	 * @param Tx_Flux_Form_Container_Grid $grid
	 */
	public function setGrid(Tx_Flux_Form_Container_Grid $grid) {
		$this->grid = $grid;
	}

	/**
	 * @param integer $uid
	 * @return array|FALSE
	 */
	protected function loadRecordFromDatabase($uid) {
		$uid = intval($uid);
		$tableName = $this->tableName;
		$resource = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $tableName, "uid = '" . $uid . "'");
		$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource);
		return $record;
	}

	/**
	 * @param array $record
	 * @return array
	 */
	protected function loadRecordTreeFromDatabase($record) {
		$parentFieldName = $this->getParentFieldName($record);
		if (FALSE === isset($record[$parentFieldName])) {
			$record[$parentFieldName] = $this->getParentFieldValue($record);
		}
		$records = array();
		while ($record[$parentFieldName] > 0) {
			$record = $this->loadRecordFromDatabase($record[$parentFieldName]);
			$parentFieldName = $this->getParentFieldName($record);
			array_push($records, $record);
		}
		$records = array_reverse($records);
		return $records;
	}

}
