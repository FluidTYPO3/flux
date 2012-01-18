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
 * ************************************************************* */

/**
 * ViewHelper used to render the FlexForm definition
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_RenderFieldViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('config', 'array', 'Configuration of the field');
	}

	/**
	 * Render method
	 *
	 * @return string
	 */
	public function render() {
		$config = $this->arguments['config'];
		return $this->getCustomizedConfiguration($config);
	}

	/**
	 * Gets XML for any wizards defined in $config
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getWizardConfiguration($config) {
		if (count($config['wizards']) == 0) {
			return NULL;
		}
		$xml = "<wizards type='array'>" . LF;
		foreach ($config['wizards'] as $name=>$wizard) {
			$xml .= "<{$name} type='array'>";
			if (is_array($wizard) === FALSE) {
				$xml .= $wizard;
			} else {
				$xml .= LF;
				foreach ($wizard as $fieldName=>$fieldValue) {
					$xml .= "<{$fieldName}>{$fieldValue}</{$fieldName}>" . LF;
				}
				$xml .= LF;
			}
			$xml .= "</{$name}>" .LF;
		}
		$xml .= "</wizards>" . LF;
		return $xml;
	}

	/**
	 * Wrapper to render XML for a FlexForm field based on $config
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getCustomizedConfiguration($config) {
		$type = $config['type'];
		$method = "get" . ucfirst($type) . "Configuration";
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), array($config));
		} else {
			throw new Exception('Unsupported field type in Fluid FCE: ' . $type);
		}
	}

	/**
	 * Render an input FlexForm field XML
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getInputConfiguration($config) {
		$wizards = $this->getWizardConfiguration($config);
		$xml = <<< XML
<label>{$config['label']}</label>
<required>{$config['required']}</required>
<config>
	<type>{$config['type']}</type>
	<default>{$config['default']}</default>
	<size>{$config['size']}</size>
	<eval>{$config['eval']}</eval>
	{$wizards}
</config>
XML;
		return $xml;
	}

	/**
	 * Render a select FlexForm field XML
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getSelectConfiguration($config, $addedConfig=NULL) {
		$wizards = $this->getWizardConfiguration($config);
		if ($config['items']) {
			$switchedConfig = '<items type="array">' . LF;
			foreach ($config['items'] as $iteration=>$set) {
				if (is_array($set) === FALSE) {
					$set = array($set, $set);
				} else if (count($set) == 1) {
					$set[1] = $set[0]; // option value becomes label
				}
				$switchedConfig .= '<numIndex type="array" index="' . $iteration . '">' . LF;
				$switchedConfig .= '	<numIndex index="0">' . $set[1] . '</numIndex>' . LF;
				$switchedConfig .= '	<numIndex index="1">' . $set[0] . '</numIndex>' . LF;
				$switchedConfig .= '</numIndex>' . LF;
			}
			$switchedConfig .= '</items>' . LF;
		} else if ($config['table']) {
			$switchedConfig = implode(LF, array(
				$config['table'] ? "<foreign_table>{$config['table']}</foreign_table>" : NULL,
				$config['foreign_table_where'] ? "<foreign_table_where>{$config['condition']}</foreign_table_where>" : NULL,
				$config['mm'] ? "<MM>{$config['mm']}</MM>" : NULL,
			));
		}
		if ($config['itemsProcFunc']) {
			$itemsProcFunc = '<itemsProcFunc>' . $config['itemsProcFunc'] . '</itemsProcFunc>';
		}
		if ($config['suggest']) {
			$suggest = "<wizards><suggest><type>suggest</type></suggest></wizards>";
		}
		if ($config['requestUpdate'] === TRUE) {
			$onChange = "<onChange>reload</onChange>" . LF;
		}
		$xml = <<< XML
<label>{$config['label']}</label>
<required>{$config['required']}</required>
{$onChange}
<config>
	<type>{$config['type']}</type>
	<minitems>{$config['minitems']}</minitems>
	<maxitems>{$config['maxitems']}</maxitems>
	<size>{$config['size']}</size>
	<multiple>{$config['multiple']}</multiple>
	<show_thumbs>{$config['show_thumbs']}</show_thumbs>
	{$itemsProcFunc}
	{$switchedConfig}
	{$addedConfig}
	{$wizards}
	{$suggest}
</config>
XML;
		return $xml;
	}

	/**
	 * Render a textarea/RTE FlexForm field XML
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getTextConfiguration($config) {
		$wizards = $this->getWizardConfiguration($config);
		$xml = <<< XML
<label>{$config['label']}</label>
<required>{$config['required']}</required>
<config>
	<type>{$config['type']}</type>
	<default>{$config['default']}</default>
	<cols>{$config['cols']}</cols>
	<rows>{$config['rows']}</rows>
	{$wizards}
</config>
<defaultExtras>{$config['defaultExtras']}</defaultExtras>
XML;
		return $xml;
	}

	/**
	 * Render a checkbox FlexForm field XML
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getCheckConfiguration($config) {
		if ($config['requestUpdate'] === TRUE) {
			$onChange = "<onChange>reload</onChange>" . LF;
		}
		$wizards = $this->getWizardConfiguration($config);
		$xml = <<< XML
<label>{$config['label']}</label>
<required>{$config['required']}</required>
{$onChange}
<config>
	<type>{$config['type']}</type>
	{$wizards}
</config>
XML;
		return $xml;
	}

	/**
	 * Render a group FlexForm field XML
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getGroupConfiguration($config) {
		$added = <<< XML
	<allowed>{$config['allowed']}</allowed>
	<internal_type>{$config['internal_type']}</internal_type>
	<uploadfolder>{$config['uploadfolder']}</uploadfolder>
XML;
		$xml = $this->getSelectConfiguration($config, $added);
		return $xml;
	}

	/**
	 * Render a tree field XML
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getTreeConfiguration($config) {
		$config['type'] = 'select';
		$expandAll = $config['expandAll'] ? 'TRUE' : 'FALSE';
		$showHeader = $config['showHeader'] ? 'TRUE' : 'FALSE';
		$parentField = $config['parentField'];
		$added = <<< XML
<renderMode>tree</renderMode>
<treeConfig>
	<parentField>$parentField</parentField>
	<appearance>
		<expandAll>$expandAll</expandAll>
		<showHeader>$showHeader</showHeader>
		<width>400</width>
	</appearance>
</treeConfig>
XML;
		$xml = $this->getSelectConfiguration($config, $added);
		return $xml;
	}

	/**
	 * Render a userFunction to return FlexForm field XML
	 *
	 * @param array $config
	 * @return string
	 */
	protected function getUserConfiguration($config) {
		$wizards = $this->getWizardConfiguration($config);
		$xml = <<< XML
<label>{$config['label']}</label>
<required>{$config['required']}</required>
<config>
	<type>{$config['type']}</type>
	<userFunc>{$config['userFunc']}</userFunc>
	{$wizards}
</config>
XML;
		return $xml;
	}

}

?>