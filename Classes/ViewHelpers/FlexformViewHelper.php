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
 * FlexForm configuration container ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers
 */
class Tx_Flux_ViewHelpers_FlexformViewHelper extends Tx_Flux_Core_ViewHelper_AbstractFlexformViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('id', 'string', 'Identifier of this Flexible Content Element, [a-z0-9\-] allowed', TRUE);
		$this->registerArgument('label', 'string', 'Label for the FlexForm, can be LLL: value. Optional - if not specified, Flux ' .
			'tries to detect an LLL label named "flux.fluxFormId", in scope of extension rendering the Flux form.', FALSE, NULL);
		$this->registerArgument('description', 'string', 'Short description of this content element', FALSE, NULL);
		$this->registerArgument('icon', 'string', 'Optional icon file to use when displaying this content element in the new content element wizard', FALSE, '../typo3conf/ext/flux/Resources/Public/Icons/Plugin.png');
		$this->registerArgument('mergeValues', 'boolean', 'If TRUE, enables overriding of record values with corresponding values from this FlexForm', FALSE, FALSE);
		$this->registerArgument('enabled', 'boolean', 'If FALSE, makes the FCE inactive', FALSE, TRUE);
		$this->registerArgument('wizardTab', 'string', 'Optional tab name (usually extension key) in which to place the content element in the new content element wizard', FALSE, 'FCE');
		$this->registerArgument('compact', 'boolean', 'If TRUE, disables sheet usage in the form. WARNING! AVOID DYNAMIC VALUES AT ALL COSTS! Toggling this option is DESTRUCTIVE to variables currently saved in the database!', FALSE, FALSE);
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		$icon = $this->arguments['icon'];
		if (0 === strpos($icon, 'EXT:')) {
			$icon = t3lib_div::getFileAbsFileName($icon);
		}
		$id = $this->arguments['id'];
		$allowed = 'a-z';
		$pattern = '/[^' . $allowed . ']+/i';
		if (preg_match($pattern, $id)) {
			$this->configurationService->message('Flux FlexForm with id "' . $id . '" uses invalid characters in the ID; valid characters
				are: "' . $allowed . '" and the pattern used for matching is "' . $pattern . '". This bad ID name will prevent
				you from utilising some features, fx automatic LLL reference building, but is not fatal', t3lib_div::SYSLOG_SEVERITY_NOTICE);
		}
		$description = $this->arguments['description'];
		if (TRUE === empty($description)) {
			$extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
			$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionName);
			$description = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xml:flux.' . $id . '.description';
		}
		$this->setStorage(array(
			'label' => $this->getLabel(),
			'description' => $description,
			'icon' => $icon,
			'compact' => $this->arguments['compact'],
			'enabled' => $this->arguments['enabled'],
			'wizardTab' => $this->arguments['wizardTab'],
			'mergeValues' => $this->arguments['mergeValues'],
			'id' => $id,
			'fields' => array(),
			'hidefields' => array(),
		));
		$this->renderChildren();
		return '';
	}

}
