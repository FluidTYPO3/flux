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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\PathUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\View\PreviewView;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 * @subpackage Provider
 */
class AbstractProvider implements ProviderInterface {

	const FORM_CLASS_PATTERN = '%s\\Form\\%s\\%sForm';

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
	 * @var string|NULL
	 */
	protected $packageName = NULL;

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
	 * @var WorkspacesAwareRecordService
	 */
	protected $recordService;

	/**
	 * @var PreviewView
	 */
	protected $previewView;

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
	 * @param WorkspacesAwareRecordService $recordService
	 * @return void
	 */
	public function injectRecordService(WorkspacesAwareRecordService $recordService) {
		$this->recordService = $recordService;
	}

	/**
	 * @param PreviewView $previewView
	 * @return void
	 */
	public function injectPreviewView(PreviewView $previewView) {
		$this->previewView = $previewView;
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
		$typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$signature = str_replace('_', '', $extensionKey);
		$variables = array(
			'record' => $row,
		);
		if (TRUE === isset($typoScript['plugin.']['tx_' . $signature . '.']['settings.'])) {
			$variables['settings'] = GeneralUtility::removeDotsFromTS($typoScript['plugin.']['tx_' . $signature . '.']['settings.']);
		}

		// Special case: when saving a new record variable $row[$fieldName] is already an array
		// and must not be processed by the configuration service.
		if (FALSE === is_array($row[$fieldName])) {
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
	public function getForm(array $row) {
		if (NULL !== $this->form) {
			return $this->form;
		}
		$formClassName = $this->resolveFormClassName($row);
		if (NULL !== $formClassName) {
			$form = call_user_func_array(array($formClassName, 'create'), array($row));
		} else {
			$templateSource = $this->getTemplateSource($row);
			if (NULL === $templateSource) {
				// Early return: no template file, no source - NULL expected.
				return NULL;
			}
			$section = $this->getConfigurationSectionName($row);
			$controllerName = 'Flux';
			$formName = 'form';
			$paths = $this->getTemplatePaths($row);
			$extensionKey = $this->getExtensionKey($row);
			$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);

			$variables = $this->getViewVariables($row);
			$view = $this->configurationService->getPreparedExposedTemplateView($extensionName, $controllerName, $paths, $variables);

			$view->setTemplateSource($templateSource);
			$form = $view->getForm($section, $formName);
		}

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
		$variables = $this->getViewVariables($row);
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
	 * Get the source of the template to be rendered. Default implementation
	 * returns the source of whichever filename is returned from the Provider.
	 * Overriding this method in other implementations allows the Provider
	 * to operate without a template file.
	 *
	 * @param array $row
	 * @return string|NULL
	 */
	public function getTemplateSource(array $row) {
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		return TRUE === file_exists($templatePathAndFilename) ? file_get_contents($templatePathAndFilename) : NULL;
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
				/** @var \DOMElement $fieldNode */
				if (TRUE === in_array($fieldNode->getAttribute('index'), $removals)) {
					$fieldNode->parentNode->removeChild($fieldNode);
				}
			}
			// Assign a hidden ID to all container-type nodes, making the value available in templates etc.
			foreach ($dom->getElementsByTagName('el') as $containerNode) {
				/** @var \DOMElement $containerNode */
				$hasIdNode = FALSE;
				if (0 < $containerNode->attributes->length) {
					// skip <el> tags reserved for other purposes by attributes; only allow pure <el> tags.
					continue;
				}
				foreach ($containerNode->childNodes as $fieldNodeInContainer) {
					/** @var \DOMElement $fieldNodeInContainer */
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
		$previewContent = $this->previewView->getPreview($this, $row);
		return array(NULL, $previewContent, empty($previewContent));
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
				$values = $this->unsetInheritedValues($field, $values);
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
	 * @param FormInterface $field
	 * @param array $values
	 * @return array
	 */
	protected function unsetInheritedValues(FormInterface $field, $values) {
		$name = $field->getName();
		$inherit = (boolean) $field->getInherit();
		$inheritEmpty = (boolean) $field->getInheritEmpty();
		$empty = (TRUE === empty($values[$name]) && $values[$name] !== '0' && $values[$name] !== 0);
		if (FALSE === $inherit || (TRUE === $inheritEmpty && TRUE === $empty)) {
			unset($values[$name]);
		}
		return $values;
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
		return $this->packageName;
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
	 * @return array|FALSE
	 */
	protected function loadRecordFromDatabase($uid) {
		$uid = intval($uid);
		$tableName = $this->tableName;
		return $this->recordService->getSingle($tableName, '*', $uid);
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
	 * Use by TceMain to track method calls to providers for a certain $id.
	 * Every provider should only be called once per method / $id.
	 * When TceMain has called the provider it will call this method afterwards.
	 *
	 * @param string $methodName
	 * @param mixed $id
	 * @return void
	 */
	public function trackMethodCall($methodName, $id) {
		self::trackMethodCallWithClassName(get_class($this), $methodName, $id);
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
	 * @return boolean
	 */
	public function shouldCall($methodName, $id) {
		return self::shouldCallWithClassName(get_class($this), $methodName, $id);
	}

	/**
	 * Internal method. See trackMethodCall.
	 * This is used by flux own provider to make sure on inheritance they are still only executed once.
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param mixed $id
	 * @return void
	 */
	protected function trackMethodCallWithClassName($className, $methodName, $id) {
		$cacheKey = $className . $methodName . $id;
		self::$trackedMethodCalls[$cacheKey] = TRUE;
	}

	/**
	 * Internal method. See shouldCall.
	 * This is used by flux own provider to make sure on inheritance they are still only executed once.
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param mixed $id
	 * @return boolean
	 */
	protected function shouldCallWithClassName($className, $methodName, $id) {
		$cacheKey = $className . $methodName . $id;
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
