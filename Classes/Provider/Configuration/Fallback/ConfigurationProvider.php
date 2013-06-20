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
 * Tries to match parameters in $row against deprecated registration function
 * usages, then automatically fills out all necessary details if a configuration
 * is detected.
 *
 * Functions as deprecation fallback to make sure all further processing happens
 * through the ConfigurationProvider built from this class.
 *
 * @package Flux
 * @subpackage Provider/Configuration/Fallback
 */
class Tx_Flux_Provider_Configuration_Fallback_ConfigurationProvider extends Tx_Flux_Provider_AbstractConfigurationProvider implements Tx_Flux_Provider_ConfigurationProviderInterface {

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
        if ($templatePaths === NULL) {
            $this->templatePaths = $templatePaths;
        } else {
            $tempTemplatePaths = array();

            foreach ($templatePaths as $pathType => $templatePath) {
                if (0 === strpos($templatePath, 'EXT:')) {
                    $tempTemplatePaths[$pathType] = t3lib_div::getFileAbsFileName($templatePath);
                } else {
                    $tempTemplatePaths[$pathType] = $templatePath;
                }
            }

            $this->templatePaths = $tempTemplatePaths;
        }
	}

	/**
	 * @param string|NULL $configurationSectionName
	 * @return void
	 */
	public function setConfigurationSectionName($configurationSectionName) {
		$this->configurationSectionName = $configurationSectionName;
	}

}
