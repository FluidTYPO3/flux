<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
***************************************************************/

/**
 * FlexForm integration Service
 *
 * Capable of returning instances of DomainObjects or ObjectStorage from
 * FlexForm field values if the type of field is a database relation and the
 * table it uses is one associated with Extbase.
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_FlexForm implements t3lib_Singleton {

	/**
	 * @var string
	 */
	protected $raw;

	/**
	 * @var array
	 */
	protected $contentObjectData;

	/**
	 * @var Tx_Fed_Utility_DomainObjectInfo
	 */
	protected $infoService;

	/**
	 *
	 * @var Tx_Extbase_Configuration_FrontendConfigurationManager
	 */
	protected $configuration;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Property_Mapper Tx_Extbase_Property_Mapper
	 */
	protected $propertyMapper;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @param Tx_Fed_Utility_DomainObjectInfo $infoService
	 */
	public function injectInfoService(Tx_Fed_Utility_DomainObjectInfo $infoService) {
		$this->infoService = $infoService;
	}

	/**
	 * @param Tx_Extbase_Configuration_FrontendConfigurationManager $configurationManager
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_FrontendConfigurationManager $configurationManager) {
		$this->configuration = $configurationManager;
	}

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Extbase_Property_Mapper $propertyMapper
	 */
	public function injectPropertyMapper(Tx_Extbase_Property_Mapper $propertyMapper) {
		$this->propertyMapper = $propertyMapper;
	}

	/**
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Initialization
	 */
	public function initializeObject() {
		$contentObject = $this->configuration->getContentObject();
		$this->contentObjectData = $contentObject->data;
		$this->raw = $this->contentObjectData['pi_flexform'];
	}

	/**
	 * @param array $data
	 */
	public function setContentObjectData($data) {
		$this->contentObjectData = $data;
		$this->raw = $this->contentObjectData['pi_flexform'];
	}

	/**
	 * Uses "transform" property on each member of $fieldArrayContainingType to
	 * properly type-cast each value before returning
	 *
	 * @param array $fieldArrayContainingTypes
	 * @param string $prefix
	 */
	public function getAllAndTransform($fieldArrayContainingTypes, $prefix='') {
		$all = $this->getAll();
		foreach ($fieldArrayContainingTypes as $fieldConfiguration) {
			$transformType = $fieldConfiguration['transform'];
			if ($transformType) {
				$fieldName = $fieldConfiguration['name'];
				$path = explode('.', $fieldName);
				$current =& $all;
				while ($key = array_shift($path)) {
					$current =& $current[$key];
				}
				$current = $this->digDownTransform($all, explode('.', $fieldName), $transformType);
			}
		}
		return $all;
	}

	/**
	 * Digs down path to transform final member to $dataType
	 *
	 * @param mixed $value
	 * @param array $keysLeft
	 * @param string $transformType
	 * @return mixed
	 */
	protected function digDownTransform($all, $keysLeft, $transformType) {
		$current =& $all;
		while ($key = array_shift($keysLeft)) {
			$current =& $current[$key];
		}
		return $this->transform($current, $transformType);
	}

	/**
	 * Transforms a single value to $dataType
	 *
	 * @param string $value
	 * @param string $dataType
	 */
	protected function transform($value, $dataType) {
		if ($dataType == 'int' || $dataType == 'integer') {
			return intval($value);
		} else if ($dataType == 'float') {
			return floatval($value);
		} else if ($dataType == 'array') {
			return explode(',', $value);
		} else if (strpos($dataType, 'Tx_') === 0) {
			return $this->getObjectOfType($dataType, $value);
		} else {
			return $value;
		}
	}

	/**
	 * Gets a DomainObject or QueryResult of $dataType
	 *
	 * @param string $dataType
	 * @param string $uids
	 */
	protected function getObjectOfType($dataType, $uids) {
		$uids = trim($uids, ',');
		$identifiers = explode(',', $uids);
			// fast decisions
		if (strpos($dataType, '_Domain_Model_') !== FALSE && strpos($dataType, '<') === FALSE) {
			$repositoryClassName = str_replace('_Model_', '_Repository_', $dataType) . 'Repository';
			if (class_exists($repositoryClassName)) {
				$repository = $this->objectManager->get($repositoryClassName);
				$uid = array_pop($identifiers);
				return $repository->findOneByUid($uid);
			}
		} else if (class_exists($dataType)) {
				// using constructor value to support objects like DateTime
			return $this->objectManager->get($dataType, $uids);
		}
			// slower decisions with support for type-hinted collection objects
		list ($container, $object) = explode('<', trim($dataType, '>'));
		if ($container && $object) {
			if (strpos($object, '_Domain_Model_') !== FALSE && $uids) {
				$repositoryClassName = str_replace('_Model_', '_Repository_', $object) . 'Repository';
				$repository = $this->objectManager->get($repositoryClassName);
				$query = $repository->createQuery();
				$query->matching($query->in('uid', $uids));
				return $query->execute();
			} else {
				$container = $this->objectManager->get($container);
				return $container;
			}
		} else {
				// passthrough; neither object nor type hinted collection object
			return $uids;
		}
	}

	/**
	 * Gets the value of the FlexForm fields.
	 *
	 * @return string
	 * @api
	 */
	public function getAll() {
		return $this->get(NULL);
	}

	/**
	 * Get a single field's value (or all values if no $key given;
	 * getAll() is an alias of get() with no argument)
	 *
	 * @param string $key
	 * @return mixed
	 * @api
	 */
	public function get($key=NULL) {
		$languagePointer = 'lDEF';
		$valuePointer = 'vDEF';
		$this->storage = $this->convertFlexFormContentToArray($this->raw, $languagePointer, $valuePointer);
		if ($key === NULL) {
			$arr = $this->storage;
			foreach ($arr as $k=>$v) {
				$arr[$k] = $this->get($k);
			}
			return $arr;
		}
		return $this->storage[$key];
	}

	/**
	 * Sets a value back in the flexform. For relational fields supporting
	 * Extbase DomainObjects, the $value may be an ObjectStorage or ModelObject
	 * instance - or the regular, oldschool CSV/UID string value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value) {
		$this->storage[$key] = $value;
	}

	/**
	 * Write the FlexForm back from whence it came. Returns TRUE/FALSE
	 * on success/failure.
	 *
	 * @return boolean
	 */
	public function save() {
		return FALSE;
	}

	/**
	 * Convert a DataStructure array to FlexForm XML
	 *
	 * @param array $dataStructure
	 * @return string
	 */
	public function convertDataStructureToFlexFormContent($dataStructure) {
		$flexFormTool = new t3lib_flexformtools();
		$xml = $flexFormTool->flexArray2Xml($dataStructure);
		return $xml;
	}

	/**
	 * Gets a stored FlexForm configuration and applies any dynamic values to
	 * create a current representation of the FlexForm sheet+fields array
	 *
	 * @param string $templateFile The absolute filename containing the configuration
	 * @param mixed $values Optional values to use when rendering the configuration
	 * @param string|NULL Optional section name containing the configuration
	 * @return array
	 */
	public function getFlexFormConfigurationFromFile($templateFile, $values, $section=NULL) {
		$view = $this->objectManager->get('Tx_Flux_MVC_View_ExposedStandaloneView');
		$view->setTemplatePathAndFilename($templateFile);
		$view->assignMultiple($values);
		$config = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', $section);
		return $config;
	}

	/**
	 * Updates $dataStructArray by reference, filling it with a proper data structure
	 * based on the selected template file.
	 *
	 * @param string $templateFile
	 * @param array $values
	 * @param array $paths
	 * @param array $dataStructArray
	 * @param string $section
	 * @return void
	 */
	public function convertFlexFormContentToDataStructure($templateFile, $values, $paths, &$dataStructArray, $section=NULL) {
		if (file_exists($templateFile) === FALSE) {
			$templateFile = t3lib_div::getFileAbsFileName($templateFile);
		}
                if (file_exists($templateFile) === FALSE) {
                                // only process this $dataStructArray if the specified template file exists.
                        return;
                }
		try {
			$config = $this->getFlexFormConfigurationFromFile($templateFile, $values, $section);
			$sheets = array();
			foreach ($config['fields'] as $field) {
				$groupKey = $field['sheet']['name'];
				$groupLabel = $field['sheet']['label'];
				if (is_array($sheets[$groupKey]) === FALSE) {
					$sheets[$groupKey] = array(
						'name' => $groupKey,
						'label' => $groupLabel,
						'fields' => array()
					);
				}
				array_push($sheets[$groupKey]['fields'], $field);
			}
			$flexformTemplateFile = t3lib_extMgm::extPath('flux', 'Resources/Private/Partials/AutoFlexForm.xml');
			$template = $this->objectManager->get('Tx_Fluid_View_StandaloneView');
            $template->setFormat('xml');
			$template->setTemplatePathAndFilename($flexformTemplateFile);
			$template->setPartialRootPath($paths['partialRootPath']);
			$template->setLayoutRootPath($paths['layoutRootPath']);
			$template->assignMultiple($values);
			$template->assignMultiple($config);
			$template->assign('sheets', $sheets);
			$flexformXml = $template->render();
			$dataStructArray = t3lib_div::xml2array($flexformXml);
			if (is_array($dataStructArray['sheets']) === FALSE || (count($dataStructArray['sheets']) < 1 && count($dataStructArray['sheets'][key($dataStructArray['sheets'])]) === 0)) {
				$dataStructArray = $this->getFallbackDataStructure('Form contains no fields', 'Tx_Flux_UserFunction_NoFields->renderField');
			}
		} catch (Exception $e) {
			if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
				throw $e;
			} else {
				t3lib_div::sysLog($e->getMessage, 'flux');
				$dataStructArray = $this->getFallbackDataStructure('Error', 'Tx_Flux_UserFunction_ErrorReporter->renderField', array('exception' => $e->getMessage()));
			}
		}
	}

	/**
	 * Gets a UserFunction-based single-field datastructure which can be used
	 * to report errors, display messages, make default fields etc.
	 *
	 * @param string $title
	 * @param string $userFunction
	 * @param array $parameters
	 * @return array
	 */
	public function getFallbackDataStructure($title, $userFunction, $parameters=array()) {
		return array(
			'meta' => array(
				'langDisable' => 1
			),
			'ROOT' => array(
				'TCEforms' => array(
					'sheetTitle' => $title
				),
				'type' => 'array',
				'el' => array(
					'func' => array(
						'TCEforms' => array(
							'label' => $title,
							'required' => 0,
							'config' => array(
								'type' => 'user',
								'userFunc' => $userFunction,
								'parameters' => $parameters
							)
						)
					)
				)
			),
		);
	}

	/**
	 * Parses the flexForm content and converts it to an array
	 * The resulting array will be multi-dimensional, as a value "bla.blubb"
	 * results in two levels, and a value "bla.blubb.bla" results in three levels.
	 *
	 * Note: multi-language flexForms are not supported yet
	 *
	 * @param string $flexFormContent flexForm xml string
	 * @param string $languagePointer language pointer used in the flexForm
	 * @param string $valuePointer value pointer used in the flexForm
	 * @return array the processed array
	 */
	public function convertFlexFormContentToArray($flexFormContent, $languagePointer = 'lDEF', $valuePointer = 'vDEF') {
		$settings = array();

		$flexFormArray = t3lib_div::xml2array($flexFormContent);
		$flexFormArray = (isset($flexFormArray['data']) && is_array($flexFormArray['data']) ? $flexFormArray['data'] : array());
		foreach(array_values($flexFormArray) as $languages) {
			if (!is_array($languages[$languagePointer])) {
				continue;
			}

			foreach($languages[$languagePointer] as $valueKey => $valueDefinition) {
				if (strpos($valueKey, '.') === false) {
					$settings[$valueKey] = $this->walkFlexFormNode($valueDefinition, $valuePointer);
				} else {
					$valueKeyParts = explode('.', $valueKey);
					$currentNode =& $settings;

					foreach ($valueKeyParts as $valueKeyPart) {
						$currentNode =& $currentNode[$valueKeyPart];
					}

					if (is_array($valueDefinition)) {
						if (array_key_exists($valuePointer, $valueDefinition)) {
							$currentNode = $valueDefinition[$valuePointer];
						} else {
							$currentNode = $this->walkFlexFormNode($valueDefinition, $valuePointer);
						}
					} else {
						$currentNode = $valueDefinition;
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * Parses a flexForm node recursively and takes care of sections etc
	 *
	 * @param array $nodeArray The flexForm node to parse
	 * @param string $valuePointer The valuePointer to use for value retrieval
	 * @return array
	 */
	public function walkFlexFormNode($nodeArray, $valuePointer = 'vDEF') {
		if (is_array($nodeArray)) {
			$return = array();

			foreach ($nodeArray as $nodeKey => $nodeValue) {
				if ($nodeKey === $valuePointer) {
					return $nodeValue;
				}

				if (in_array($nodeKey, array('el', '_arrayContainer'))) {
					return $this->walkFlexFormNode($nodeValue, $valuePointer);
				}

				if (substr($nodeKey, 0, 1) === '_') {
					continue;
				}

				if (strpos($nodeKey, '.')) {
					$nodeKeyParts = explode('.', $nodeKey);
					$currentNode =& $return;

					for ($i = 0; $i < (count($nodeKeyParts) - 1); $i++) {
						$currentNode =& $currentNode[$nodeKeyParts[$i]];
					}

					$newNode = array(next($nodeKeyParts) => $nodeValue);
					$currentNode = $this->walkFlexFormNode($newNode, $valuePointer);
				} else if (is_array($nodeValue)) {
					if (array_key_exists($valuePointer, $nodeValue)) {
						$return[$nodeKey] = $nodeValue[$valuePointer];
					} else {
						$return[$nodeKey] = $this->walkFlexFormNode($nodeValue, $valuePointer);
					}
				} else {
					$return[$nodeKey] = $nodeValue;
				}
			}
			return $return;
		}

		return $nodeArray;
	}

}

?>
