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
 *****************************************************************/

/**
 * Base class for all FlexForm related ViewHelpers
 *
 * @package Flux
 * @subpackage Core/ViewHelper
 */
abstract class Tx_Flux_Core_ViewHelper_AbstractFlexformViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper implements ArrayAccess {

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var array
	 */
	protected $structure = array();

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * Inject a TagBuilder
	 * (needed for compatibility w/ TYPO3 4.5 LTS where no inject method for TagBuilder exists)
	 *
	 * @param Tx_Fluid_Core_ViewHelper_TagBuilder $tagBuilder Tag builder
	 * @return void
	 */
	public function injectTagBuilder(Tx_Fluid_Core_ViewHelper_TagBuilder $tagBuilder) {
		$this->tag = $tagBuilder;
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$this->renderChildren();
		$this->configuration = $this->renderConfiguration();
		$this->structure = $this->createStructure();
		$this->addField($this->configuration);
	}

	/**
	 * @param array $config
	 * @return void
	 */
	protected function addField($config = array()) {
		if ($this->viewHelperVariableContainer->exists('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section') === TRUE) {
			$section = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section');
		} else {
			$section = NULL;
		}
		if (is_array($section) === TRUE) {
			$this->configuration['sectionObjectName'] = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionObjectName');
			array_push($section['fields'], clone $this);
			$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section', $section);
		} else {
			$storage = (array) $this->getStorage();
			array_push($storage['fields'], clone $this);
			$this->setStorage($storage);
		}
	}

	/**
	 * @param array $config
	 * @return void
	 */
	protected function addContentArea($config) {
		$storage = $this->getStorage();
		$row = count($storage['grid']) - 1;
		$col = count($storage['grid'][$row]) - 1;
		array_push($storage['grid'][$row][$col]['areas'], $config);
		$this->setStorage($storage);
	}

	/**
	 * @return void
	 */
	protected function addGridRow() {
		$storage = $this->getStorage();
		array_push($storage['grid'], array());
		$this->setStorage($storage);
	}

	/**
	 * @param array $config
	 * @return void
	 */
	protected function addGridColumn($config) {
		$storage = $this->getStorage();
		$row = count($storage['grid']) - 1;
		array_push($storage['grid'][$row], $config);
		$this->setStorage($storage);
	}

	/**
	 * Get the internal FCE storage array
	 * @return array
	 */
	protected function getStorage() {
		return $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage');
	}

	/**
	 * Set the internal FCE storage array
	 * @param array $storage
	 * @return void
	 */
	protected function setStorage($storage) {
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', $storage);
	}

	/**
	 * Stub: overridden by every FlexForm ViewHelper
	 * @return array
	 */
	protected function getBaseConfig() {
		return array();
	}

	/**
	 * @return string
	 */
	protected function getLabel() {
		if (TRUE === isset($this->arguments['label']) && FALSE === empty($this->arguments['label'])) {
			return $this->arguments['label'];
		}
		if (TRUE === isset($this->arguments['name'])) {
			$name = $this->arguments['name'];
		} else {
			$name = NULL;
		}
		$prefix = '';
		if (TRUE === $this instanceof Tx_Flux_ViewHelpers_Flexform_SheetViewHelper) {
			$prefix = 'sheets';
		} elseif (TRUE === $this instanceof Tx_Flux_ViewHelpers_Flexform_SectionViewHelper) {
			$prefix = 'sections';
		} elseif (TRUE === $this instanceof Tx_Flux_ViewHelpers_Flexform_ContentViewHelper) {
			$prefix = 'areas';
		} elseif (TRUE === $this instanceof Tx_Flux_ViewHelpers_Flexform_ObjectViewHelper) {
			$prefix = 'objects';
		} elseif (TRUE === $this instanceof Tx_Flux_ViewHelpers_FlexformViewHelper) {
			$name = $this->arguments['id'];
			$id = $this->arguments['id'];
		} elseif (TRUE === $this instanceof Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper) {
			if ($this->viewHelperVariableContainer->exists('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionObjectName')) {
				$prefix = 'objects.' . $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionObjectName');
			} else {
				$prefix = 'fields';
			}
		}
		$extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
		if (TRUE === empty($extensionName)) {
			$this->configurationService->message('Wanted to generate an automatic LLL label for field "' . $name . '" ' .
				'but there was no extension name stored in the RenderingContext.', t3lib_div::SYSLOG_SEVERITY_FATAL);
		}
		if (FALSE === isset($id)) {
			$storage = $this->getStorage();
			$id = $storage['id'];
		}
		$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionName);
		$filePrefix = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xml';
		$labelIdentifier = 'flux.' . $id . (TRUE === empty($prefix) ? '' : '.' . $prefix . '.');
		$labelIdentifier .= $this->arguments['name'];
		$this->updateLanguageSourceFileIfUpdateFeatureIsEnabledAndIdentifierIsMissing($filePrefix, $labelIdentifier, $id);
		return $filePrefix . ':' . $labelIdentifier;
	}

	/**
	 * @param string $file
	 * @param string $identifier
	 * @param string $id
	 */
	private function updateLanguageSourceFileIfUpdateFeatureIsEnabledAndIdentifierIsMissing($file, $identifier, $id) {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['rewriteLanguageFiles']) {
			return;
		}
		$this->configurationService->message('Generated automatic LLL path for entity called "' . $identifier . '" which is a ' .
			get_class($this), t3lib_div::SYSLOG_SEVERITY_INFO, 'Flux FlexForm LLL label generation');
		$debugTitle = 'Flux LLL file rewriting';
		$allowed = 'a-z\.';
		$pattern = '/[^' . $allowed . ']+/i';
		if (preg_match($pattern, $id) || preg_match($pattern, $identifier)) {
			$this->configurationService->message('Cowardly refusing to create an invalid LLL reference called "' . $identifier . '" ' .
				' in a Flux form called "' . $id . '" - one or both contains invalid characters. Allowed: dots and "' .
				$allowed . '".', t3lib_div::SYSLOG_SEVERITY_NOTICE, $debugTitle);
			return;
		}
		$file = substr($file, 4);
		$filePathAndFilename = t3lib_div::getFileAbsFileName($file);
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->preserveWhiteSpace = FALSE;
		$dom->load($filePathAndFilename);
		$dom->formatOutput = TRUE;
		foreach ($dom->getElementsByTagName('languageKey') as $languageNode) {
			$nodes = array();
			foreach ($languageNode->getElementsByTagName('label') as $labelNode) {
				$key = (string) $labelNode->attributes->getNamedItem('index')->firstChild->textContent;
				if ($key === $identifier) {
					$this->configurationService->message('Skipping LLL file merge for label "' . $identifier.
						'"; it already exists in file "' . $filePathAndFilename . '"');
					return;
				}
				$nodes[$key] = $labelNode;
			}
			$node = $dom->createElement('label', $identifier);
			$attribute = $dom->createAttribute('index');
			$attribute->appendChild($dom->createTextNode($identifier));
			$node->appendChild($attribute);
			$nodes[$identifier] = $node;
			ksort($nodes);
			foreach ($nodes as $labelNode) {
				$languageNode->appendChild($labelNode);
			}
		}
		$xml = $dom->saveXML();
		if (FALSE === $xml) {
			$this->configurationService->message('Skipping LLL file saving due to an error while generating the XML.',
				t3lib_div::SYSLOG_SEVERITY_FATAL);
		} else {
			$this->configurationService->message('Rewrote "' . $file . '" by adding placeholder label for "' . $identifier . '"',
				t3lib_div::SYSLOG_SEVERITY_INFO, $debugTitle);
			file_put_contents($filePathAndFilename, $xml);
		}
	}

	/**
	 * @param array $configuration
	 * @return array
	 */
	protected function getWizardStructureArray($configuration) {
		$wizardStructureArray = array();
		$wizards = t3lib_div::xml2array($configuration['wizards'], '', TRUE);
		if (isset($wizards['_DOCUMENT_TAG'])) {
			$wizards = array($wizards);
		}
		foreach ($wizards as $wizard) {
			$key = $wizard['_DOCUMENT_TAG'];
			$wizardStructureArray[$key] = $wizard;
		}
		return $wizardStructureArray;
	}

	/**
	 * Creates a TCEforms configuration array based on the
	 * configuration stored in this ViewHelper. Calls the
	 * expected-to-be-overridden stub method getConfiguration()
	 * to return the TCE field configuration - see that method
	 * for information about how to implement that method.
	 *
	 * @return array
	 */
	public function createStructure() {
		if (TRUE === method_exists($this, 'getBaseConfig')) {
			$configuration = $this->getBaseConfig();
		} else {
			$configuration = array();
		}
		$fieldStructureArray = array(
			'TCEforms' => array(
				'label' => $configuration['label'],
				'required' => $this->arguments['required'],
				'config' => $this->renderConfiguration(),
				'displayCond' => $this->arguments['displayCond']
			)
		);
		if ($configuration['wizards']) {
			$fieldStructureArray['TCEforms']['config']['wizards'] = $this->getWizardStructureArray($configuration);
		}
		if ($configuration['requestUpdate']) {
			$fieldStructureArray['TCEforms']['onChange'] = 'reload';
		}
		return $fieldStructureArray;
	}

	/**
	 * Stub: Should be overridden by every field type in order
	 * to allow it to return its own special configuration array.
	 * The returned configuration array should be the array usually
	 * placed under the "config" index in an TCA array. Example:
	 * array("type" => "input", "default" => "Default value")
	 *
	 * @return array
	 */
	public function renderConfiguration() {
		return $this->configuration;
	}

	/**
	 * @param mixed $offset
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return isset($this->configuration[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->configuration[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->configuration[$offset] = $value;
	}

	/**
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->configuration[$offset]);
	}

	/**
	 * @return array
	 */
	public function getStructure() {
		return $this->structure;
	}

}