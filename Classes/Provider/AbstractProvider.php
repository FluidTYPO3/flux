<?php
namespace FluidTYPO3\Flux\Provider;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\PathUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @package Flux
 * @subpackage Provider
 */
class AbstractProvider implements ProviderInterface {

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
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var ContentService
	 */
	protected $contentService;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param ContentService $contentService
	 * @return void
	 */
	public function injectContentService(ContentService $contentService) {
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
	 * @return Form|NULL
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
		$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
		$fieldName = $this->getFieldName($row);

		// Special case: when saving a new record variable $row[$fieldName] is already an array
		// and must not be processed by the configuration service.
		if (FALSE === is_array($row[$fieldName])) {
			$variables = $this->configurationService->convertFlexFormContentToArray($row[$fieldName]);
		} else {
			$variables = array();
		}

		$variables['record'] = $row;
		$variables = GeneralUtility::array_merge_recursive_overrule($this->templateVariables, $variables);
		$form = $this->configurationService->getFormFromTemplateFile($templatePathAndFilename, $section, $formName, $paths, $extensionName, $variables);
		$form = $this->setDefaultValuesInFieldsWithInheritedValues($form, $row);
		return $form;
	}

	/**
	 * @param array $row
	 * @return Grid
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
		$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
		$fieldName = $this->getFieldName($row);
		$variables = $this->configurationService->convertFlexFormContentToArray($row[$fieldName]);
		$variables['record'] = $this->loadRecordFromDatabase($row['uid']);
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
			return GeneralUtility::getFileAbsFileName($this->templatePathAndFilename);
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
		$cacheKey = 'values_' . md5(json_encode($row) . $fieldName);
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
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
		$merged = RecursiveArrayUtility::merge($inheritedConfiguration, $immediateConfiguration);
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
		$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
		if (FALSE === is_array($paths)) {
			$extensionKey = $this->getExtensionKey($row);
			if (FALSE === empty($extensionKey) && TRUE === ExtensionManagementUtility::isLoaded($extensionKey)) {
				$paths = $this->configurationService->getViewConfigurationForExtensionName($extensionKey);
			}
		}

		if (NULL !== $paths && FALSE === is_array($paths)) {
			$this->configurationService->message('Template paths resolved for "' . $extensionKey . '" was not an array.', GeneralUtility::SYSLOG_SEVERITY_WARNING);
			$paths = NULL;
		}

		if (NULL === $paths) {
			$extensionKey = $this->getExtensionKey($row);
			if (FALSE === empty($extensionKey) && TRUE === ExtensionManagementUtility::isLoaded($extensionKey)) {
				$paths = array(
					ExtensionManagementUtility::extPath($extensionKey, 'Resources/Private/Templates/'),
					ExtensionManagementUtility::extPath($extensionKey, 'Resources/Private/Partials/'),
					ExtensionManagementUtility::extPath($extensionKey, 'Resources/Private/Layouts/')
				);
			} else {
				$paths = array();
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
	 * @param DataHandler $reference A reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object that is currently saving the record
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, DataHandler $reference) {
		if ('update' === $operation) {
			$record = $reference->datamap[$this->tableName][$id];
			$fieldName = $this->getFieldName((array) $record);
			if (NULL === $fieldName) {
				return;
			}
			if (FALSE === isset($row[$fieldName]) || FALSE === isset($record[$fieldName]['data']) || FALSE === is_array($record[$fieldName]['data'])) {
				return;
			}
			$data = $record[$fieldName]['data'];
			$removals = array();
			foreach ($data as $sheetName => $sheetFields) {
				foreach ($sheetFields['lDEF'] as $sheetFieldName => $fieldDefinition) {
					if ('_clear' === substr($sheetFieldName, -6)) {
						array_push($removals, $sheetFieldName);
					} else {
						$clearFieldName = $sheetFieldName . '_clear';
						$clearFieldValue = (boolean) (TRUE === isset($data[$sheetName]['lDEF'][$clearFieldName]['vDEF']) ? $data[$sheetName]['lDEF'][$clearFieldName]['vDEF'] : 0);
						$inheritedValue = $this->getInheritedPropertyValueByDottedPath($row, $sheetFieldName);
						$shouldClearField = (0 < $data[$sheetName]['lDEF'][$clearFieldName]['vDEF'] || (NULL !== $inheritedValue && $inheritedValue == $fieldDefinition['vDEF']));
						if (TRUE === $shouldClearField || TRUE === $clearFieldValue) {
							array_push($removals, $sheetFieldName);
						}
					}
				}
			}
			$dom = new \DOMDocument();
			$dom->loadXML($row[$fieldName]);
			$dom->preserveWhiteSpace = FALSE;
			$dom->formatOutput = TRUE;
			foreach ($dom->getElementsByTagName('field') as $fieldNode) {
				if (TRUE === in_array($fieldNode->getAttribute('index'), $removals)) {
					$fieldNode->parentNode->removeChild($fieldNode);
				}
			}
			// Assign a hidden ID to all container-type nodes, making the value available in templates etc.
			foreach ($dom->getElementsByTagName('el') as $containerNode) {
				$hasIdNode = FALSE;
				if (0 < $containerNode->attributes->length) {
					// skip <el> tags reserved for other purposes by attributes; only allow pure <el> tags.
					continue;
				}
				foreach ($containerNode->childNodes as $fieldNodeInContainer) {
					if (FALSE === $fieldNodeInContainer instanceof \DOMElement) {
						continue;
					}
					$isFieldNode = ('field' === $fieldNodeInContainer->tagName);
					$isIdField = ('id' === $fieldNodeInContainer->getAttribute('index'));
					if ($isFieldNode && $isIdField) {
						$hasIdNode = TRUE;
						break;
					}
				}
				if (FALSE === $hasIdNode) {
					$idNode = $dom->createElement('field');
					$idIndexAttribute = $dom->createAttribute('index');
					$idIndexAttribute->nodeValue = 'id';
					$idNode->appendChild($idIndexAttribute);
					$valueNode = $dom->createElement('value');
					$valueIndexAttribute = $dom->createAttribute('index');
					$valueIndexAttribute->nodeValue = 'vDEF';
					$valueNode->appendChild($valueIndexAttribute);
					$valueNode->nodeValue = sha1(uniqid('container_', TRUE));
					$idNode->appendChild($valueNode);
					$containerNode->appendChild($idNode);
				}
			}
			$row[$fieldName] = $dom->saveXML();
			// hack-like pruning of empty-named node inserted when removing objects from a previously populated Section
			$row[$fieldName] = str_replace('<field index=""></field>', '', $row[$fieldName]);
			$reference->datamap[$this->tableName][$id][$fieldName] = $row[$fieldName];
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
	 * Perform various cleanup operations upon clearing cache
	 *
	 * @param array $command
	 * @return void
	 */
	public function clearCacheCommand($command = array()) {
		// only empty the cache when "clear configuration cache is pressed"
		if ('temp_cached' !== $command['cacheCmd']) {
			return;
		}
		if (TRUE === isset($command['uid'])) {
			return;
		}
		$files = glob(PATH_site . 'typo3temp/flux-*');
		FALSE === $files ? : array_map('unlink', $files);
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
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		if (FALSE === file_exists($templatePathAndFilename)) {
			return array(NULL, NULL, TRUE);
		}
		$extensionKey = $this->getExtensionKey($row);
		$flexformVariables = $this->getFlexFormValues($row);
		$templateVariables = $this->getTemplateVariables($row);
		$variables = RecursiveArrayUtility::merge($templateVariables, $flexformVariables);
		$paths = $this->getTemplatePaths($row);
		$form = $this->getForm($row);
		$formLabel = $form->getLabel();
		$label = LocalizationUtility::translate($formLabel, $extensionKey);
		$variables['label'] = $label;
		$variables['row'] = $row;

		$view = $this->configurationService->getPreparedExposedTemplateView($extensionKey, 'Content', $paths, $variables);
		$view->setTemplatePathAndFilename($templatePathAndFilename);

		$existingContentObject = $this->configurationManager->getContentObject();
		$contentObject = new ContentObjectRenderer();
		$contentObject->start($row, $this->getTableName($row));
		$this->configurationManager->setContentObject($contentObject);
		$previewContent = $view->renderStandaloneSection('Preview', $variables);
		$this->configurationManager->setContentObject($existingContentObject);
		$previewContent = trim($previewContent);
		$headerContent = NULL;
		return array($headerContent, $previewContent, FALSE);
	}

	/**
	 * @param Form $form
	 * @param array $row
	 * @return Form
	 */
	protected function setDefaultValuesInFieldsWithInheritedValues(Form $form, array $row) {
		foreach ($form->getFields() as $field) {
			$name = $field->getName();
			$inheritedValue = $this->getInheritedPropertyValueByDottedPath($row, $name);
			if (NULL !== $inheritedValue && TRUE === $field instanceof FieldInterface) {
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
			return TRUE === isset($inheritedConfiguration[$propertyPath]) ? ObjectAccess::getProperty($inheritedConfiguration, $propertyPath) : NULL;
		}
		return ObjectAccess::getPropertyPath($inheritedConfiguration, $propertyPath);
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
				$stop = (TRUE === $field->getStopInheritance());
				$inherit = (TRUE === $field->getInheritEmpty());
				$empty = (TRUE === empty($values[$name]) && $values[$name] !== '0' && $values[$name] !== 0);
				if (TRUE === $stop || (FALSE === $inherit && TRUE === $empty)) {
					unset($values[$name]);
				}
			}
			$data = RecursiveArrayUtility::merge($data, $values);
		}
		if (TRUE === $mergeToCache && TRUE === $this->hasCacheForMergedConfiguration($cacheKey)) {
			$data = RecursiveArrayUtility::merge(self::$cache[$cacheKey], $data);
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
	 * @return array|FALSE
	 */
	protected function loadRecordFromDatabase($uid) {
		$uid = intval($uid);
		$tableName = $this->tableName;
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $tableName, "uid = '" . $uid . "'");
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

	/**
	 * @param string $methodName
	 * @param array $row
	 */
	public function trackMethodCall($methodName, array $row) {
		$cacheKey = get_class($this). $methodName . (TRUE === isset($row['uid']) ? $row['uid'] : '');
		self::$trackedMethodCalls[$cacheKey] = TRUE;
	}

	/**
	 * @param string $methodName
	 * @param array $row
	 * @return boolean
	 */
	public function shouldCall($methodName, array $row) {
		$cacheKey = get_class($this). $methodName . (TRUE === isset($row['uid']) ? $row['uid'] : '');
		return empty(self::$trackedMethodCalls[$cacheKey]);
	}

	/**
	 * @return void
	 */
	public function reset() {
		self::$cache = array();
	}

}
