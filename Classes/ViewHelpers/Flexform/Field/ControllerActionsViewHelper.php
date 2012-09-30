<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * ControllerActions ViewHelper
 *
 * Renders a FlexForm select field with options fetched from
 * requested extensionName/pluginName and other settings.
 *
 * There are two basic ways of adding selection options:
 *
 * - You can use the "extensionName" and "pluginName" to render all
 *   possible actions from an Extbase plugin that you've defined. It
 *   doesn't have to be your own plugin - if for example you are
 *   rendering actions from EXT:news or another through your own plugin.
 * - Or you can use the "actions" argument which is an array:
 *   {ControllerName: 'action1,action2,action3', OtherControllerName: 'action1'}
 *
 * And there are a few ways to limit the options that are displayed:
 *
 * - You can use "excludeActions" to specify an array in the same
 *   syntax used by the "actions" argument, these are then excluded.
 * - You can specifiy the "controllerName" argument in which case
 *   only actions from that Controller are displayed.
 *
 * And there are a couple of ways to define/resolve labels for actions:
 *
 * - You can add an LLL label in your locallang_db file:
 *   lowercasepluginnanem.lowercasecontrollername.actionfunctionname
 *   example index: myext.articlecontroller.show
 * - You can do nothing, in which case the very first line of
 *   the PHP doc-comment of each action method is used. This value can
 *   even be an LLL:file reference (in case you don't want to use the
 *   pattern above - but beware this is somewhat expensive processing)
 * - Or you can do nothing at all, not even add a doc comment, in which
 *   case the Controller->action syntax is used instead.
 *
 * Marking actions that have required arguments (which cause errors if
 * rendered on a page that is accessible through a traditional menu) is
 * possible but is deactivated for LLL labels; if you use LLL labels
 * and your action requires an argument, be user friendly and note so
 * in the LLL label or docs as applies.
 *
 * Lastly, you can set a custom name for the field in which case the
 * value does not trigger the Extbase SwitchableControllerActions feature
 * but instead works as any other Flux FlexForm field would.
 *
 * To use the field just place it in your Flux form (but in almost all
 * cases leave out the "name" argument which is required on all other
 * field types at the time of writing this). Where the field is placed
 * is not important; the order and the sheet location don't matter.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_ControllerActionsViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_SelectViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->overrideArgument('name', 'string', 'Name of the field', FALSE, 'switchableControllerActions');
		$this->registerArgument('extensionName', 'string', 'Name of the Extbase extension that contains the Controller to parse, ex. MyExtension');
		$this->registerArgument('pluginName', 'string', 'Name of the Extbase plugin that contains Controller definitions to parse, ex. MyPluginName');
		$this->registerArgument('controllerName', 'string', 'Optional extra limiting of actions displayed - if used, field only displays actions for this controller name - ex Article(Controller) or FrontendUser(Controller) - the Controller part is implied');
		$this->registerArgument('actions', 'array', 'Array of "ControllerName" => "csv,of,actions" which are allowed. If used, does not require the use of an ExtensionName and PluginName (will use the one specified in your current plugin automatically)');
		$this->registerArgument('excludeActions', 'array', 'Array of "ControllerName" => "csv,of,actions" which must be excluded', FALSE, array());
		$this->registerArgument('prefixOnRequiredArguments', 'string', 'A short string denoting that the method takes arguments, ex * (which should then be explained in the documentation for your extension about how to setup your plugins', FALSE, '*');
		$this->registerArgument('disableLocalLanguageLabels', 'boolean', 'If TRUE, disables LLL label usage and just uses the class comment or Controller->action syntax', FALSE, FALSE);
		$this->registerArgument('localLanguageFileRelativePath', 'string', 'Relative (from extension $extensionName) path to locallang file containing the action method labels', FALSE, '/Resources/Private/Language/locallang_db.xml');
	}

	/**
	 * Render method
	 *
	 * @return void
	 * @throws Exception
	 */
	public function render() {
		$extensionName = $this->arguments['extensionName'];
		$pluginName = $this->arguments['pluginName'];
		$actions = $this->arguments['actions'];
		if (empty($extensionName) === TRUE && empty($pluginName) === TRUE && empty($actions) === TRUE) {
			throw new Exception('Either "actions", or both "extensionName" and "pluginName" must be used on flux:flexform.field.switchableControllerActions. None were found.', 1346514748);
		}
		if (is_array($actions) === FALSE) {
			if (empty($extensionName) === TRUE || empty($pluginName) === TRUE) {
				throw new Exception('Both extensionName (' . $extensionName . ') and pluginName (' . $pluginName . ') must be specified, one or both were empty', 1346519840);
			}
			$actions = $this->getActionsForExtensionNameAndPluginName($extensionName, $pluginName);
		}
		$config = $this->getFieldConfig();
		$config['type'] = 'Select';
		$config['items'] = $this->renderItemsForActions($actions);
		$this->addField($config);
	}

	/**
	 * Reads a list of allowed actions for $extensionName's plugin $pluginName
	 *
	 * @param string $extensionName
	 * @param string $pluginName
	 * @return array
	 */
	protected function getActionsForExtensionNameAndPluginName($extensionName, $pluginName) {
		$actions = (array) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$extensionName]['plugins'][$pluginName]['controllers']; //['actions']; //[$controllerName] = array('actions' => t3lib_div::trimExplode(',', $actionsList));
		foreach ($actions as $controllerName => $definitions) {
			$actions[$controllerName] = $definitions['actions'];
		}
		return $actions;
	}

	/**
	 * Renders the TCA-style items array based on the Extbase FlexForm-style
	 * definitions of selectable actions (specified manually or read based on
	 * ViewHelper arguments)
	 *
	 * @param array $actions
	 * @return array
	 */
	protected function renderItemsForActions(array $actions) {
		$exclusions = $this->arguments['excludeActions'];
		foreach ($exclusions as $controllerName => $actionsCommaSeparated) {
			$exclusions[$controllerName] = t3lib_div::trimExplode(',', $actionsCommaSeparated, TRUE);
		}
		$items = array();
		$extensionName = $this->arguments['extensionName'];
		$pluginName = $this->arguments['pluginName'];
		$limitByControllerName = $this->arguments['controllerName'];
		foreach ($actions as $controllerName => $actionsCommaSeparated) {
			$actions = is_array($actionsCommaSeparated) === TRUE ? $actionsCommaSeparated : t3lib_div::trimExplode(',', $actionsCommaSeparated, TRUE);
			if ($extensionName && $pluginName) {
				$controllerClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerName . 'Controller';
				$controllerClassReflection = new ReflectionClass($controllerClassName);
			} else {
				$controllerClassName = NULL;
				$controllerClassReflection = NULL;
			}
			foreach ($actions as $actionName) {
				$hasRequiredArguments = FALSE;
				$hasLocalLanguageLabel = FALSE;
				if (is_array($exclusions[$controllerName]) === TRUE && in_array($actionName, $exclusions[$controllerName]) === TRUE) {
					continue;
				} elseif ($limitByControllerName && $controllerName !== $limitByControllerName) {
					continue;
				} elseif ($controllerClassReflection) {
					if (method_exists($controllerClassName, $actionName . 'Action') === FALSE) {
						continue;
					}
					/** @var ReflectionMethod $methodReflection */
					$methodReflection = $controllerClassReflection->getMethod($actionName . 'Action');
					$hasRequiredArguments = (boolean) ($methodReflection->getNumberOfRequiredParameters() > 0);
					$labelPath = strtolower($pluginName . '.' . $controllerName . '.' . $actionName);
					if ($this->arguments['disableLocalLanguageLabels'] || file_exists(t3lib_extMgm::extPath(t3lib_div::camelCaseToLowerCaseUnderscored($extensionName), $this->arguments['localLanguageFileRelativePath'])) === FALSE) {
						$line = array_shift(explode("\n", trim($methodReflection->getDocComment(), "/*\n")));
						$label = trim(trim($line), '* ');
						if (substr($label, 0, 1) === '@') {
							$label = NULL;
						}
					} else {
						$label = 'LLL:EXT:' . t3lib_div::camelCaseToLowerCaseUnderscored($extensionName) . $this->arguments['localLanguageFileRelativePath'] . ':' . $labelPath;
						$hasLocalLanguageLabel = TRUE;
					}
				} else {
					$label = $actionName;
				}
				$label = trim($label);
				if (empty($label) === TRUE) {
					$label = $controllerName . '->' . $actionName;
				}
				if ($hasRequiredArguments === TRUE && $hasLocalLanguageLabel === FALSE && empty($this->arguments['prefixOnRequiredArguments']) === FALSE) {
					$label = $this->arguments['prefixOnRequiredArguments'] . ' ' . $label;
				}
				$values = array(
					$controllerName . '->' . $actionName,
					$label,
				);
				array_push($items, $values);

			}
		}
		return $items;
	}
}