<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Wouter Wolters <typo3@wouterwolters.nl>
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
 * FluidFlexFormTemplateValidator checks for a valid Fluid Flexform template.
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_FluidFlexFormTemplateValidator implements t3lib_Singleton {

	/**
	 * @var string
	 */
	protected $requiredNamespace = 'Tx_Flux_ViewHelpers';

	/**
	 * @var string
	 */
	protected $fluxFlexformPart = '<flux:flexform ';

	/**
	 * @var array
	 */
	protected $namespaces = array();

	/**
	 * Validate a Fluid FlexForm template by filename
	 *
	 * @param string $templatePathAndFilename
	 */
	public function validateFluidFlexFormTemplateFile($templatePathAndFilename) {
		$this->namespaces = array();
		$templatePathAndFilename = t3lib_div::getFileAbsFileName($templatePathAndFilename);
		$templateSource = file_get_contents($templatePathAndFilename);
		$this->validateFluidFlexFormTemplateSource($templateSource);
	}

	/**
	 * Validate a Fluid FlexForm template by source code
	 *
	 * @param string $templateSource
	 */
	public function validateFluidFlexFormTemplateSource($templateSource) {
		$this->namespaces = array();
		$this->checkRequiredNamespace($templateSource);
		$this->checkRequiredFluxFlexform($templateSource);
	}

	/**
	 * Check if required namespace exists in template source
	 *
	 * @param string $templateSource
	 */
	protected function checkRequiredNamespace($templateSource) {
		$this->extractFluxNamespaceDefinitions($templateSource);
		if (!array_search($this->requiredNamespace, $this->namespaces)) {
			throw new Exception('Required {namespace flux=Tx_Flux_ViewHelpers} not found in template', 1332869504);
		}
	}

	/**
	 * Check if required Flux ViewHelper exists in template source
	 *
	 * @param string $templateSource
	 */
	protected function checkRequiredFluxFlexform($templateSource) {
		if (strpos($templateSource, $this->fluxFlexformPart) === FALSE) {
			throw new Exception('Template does not contain a Flux FlexForm definition. There were no occurrences of the flux:flexform ViewHelper in the template.', 1332869515);
		}
	}

	/**
	 * Extract namespace definitions from template source
	 *
	 * @param string $templateSource
	 */
	protected function extractFluxNamespaceDefinitions($templateSource) {
		$matchedVariables = array();
		if (preg_match_all(Tx_Fluid_Core_Parser_TemplateParser::$SCAN_PATTERN_NAMESPACEDECLARATION, $templateSource, $matchedVariables) > 0) {
			foreach (array_keys($matchedVariables[0]) as $index) {
				$namespaceIdentifier = $matchedVariables[1][$index];
				$fullyQualifiedNamespace = $matchedVariables[2][$index];
				if (key_exists($namespaceIdentifier, $this->namespaces)) {
					throw new Tx_Fluid_Core_Parser_Exception('Namespace identifier "' . $namespaceIdentifier . '" is already registered. Do not redeclare namespaces!', 1224241246);
				}
				$this->namespaces[$namespaceIdentifier] = $fullyQualifiedNamespace;
			}
		}
	}
}
?>