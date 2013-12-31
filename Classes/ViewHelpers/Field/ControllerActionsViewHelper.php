<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * ControllerActions ViewHelper
 *
 * Renders a FlexForm select field with options fetched from
 * requested extensionName/pluginName and other settings.
 *
 * There are three basic ways of adding selection options:
 *
 * - You can use the "extensionName" and "pluginName" to render all
 *   possible actions from an Extbase plugin that you've defined. It
 *   doesn't have to be your own plugin - if for example you are
 *   rendering actions from EXT:news or another through your own plugin.
 * - Or you can use the "actions" argument which is an array:
 *   {ControllerName: 'action1,action2,action3', OtherControllerName: 'action1'}
 * - And you can extend any of the two methods above with the "subActions"
 *   parameter, which allows you to extend the allowed actions whenever
 *   the specified combination of ControllerName + actionName is encountered.
 *   Example: 		actions="{ControllerName: 'action1,action2'}"
 *            		subActions="{ControllerName: {action1: 'action3,action4'}}"
 *   Gives options: ControllerName->action1,action3,action4 with LLL values based on "action1"
 * 					ControllerName->action2 with LLL values based on "action2"
 *   By default Flux will create one option per action when reading
 *   Controller actions - using "subActions" it becomes possible to add
 *   additional actions to the list of allowed actions that the option
 *   will contain, as opposed to having only one action per option.
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
 *   lowercasepluginname.lowercasecontrollername.actionfunctionname
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
 * @subpackage ViewHelpers/Field
 */
class ControllerActionsViewHelper extends SelectViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->overrideArgument('items', 'mixed', 'Optional, full list of items to display - note: if used, this overrides any automatic option filling!', FALSE, NULL);
		$this->overrideArgument('name', 'string', 'Name of the field', FALSE, 'switchableControllerActions');
		$this->registerArgument('extensionName', 'string', 'Name of the Extbase extension that contains the Controller to parse, ex. MyExtension. In vendor based extensions use dot, ex. Vendor.MyExtension');
		$this->registerArgument('pluginName', 'string', 'Name of the Extbase plugin that contains Controller definitions to parse, ex. MyPluginName');
		$this->registerArgument('controllerName', 'string', 'Optional extra limiting of actions displayed - if used, field only displays actions for this controller name - ex Article(Controller) or FrontendUser(Controller) - the Controller part is implied', FALSE, NULL);
		$this->registerArgument('actions', 'array', 'Array of "ControllerName" => "csv,of,actions" which are allowed. If used, does not require the use of an ExtensionName and PluginName (will use the one specified in your current plugin automatically)', FALSE, array());
		$this->registerArgument('excludeActions', 'array', 'Array of "ControllerName" => "csv,of,actions" which must be excluded', FALSE, array());
		$this->registerArgument('prefixOnRequiredArguments', 'string', 'A short string denoting that the method takes arguments, ex * (which should then be explained in the documentation for your extension about how to setup your plugins', FALSE, '*');
		$this->registerArgument('disableLocalLanguageLabels', 'boolean', 'If TRUE, disables LLL label usage and just uses the class comment or Controller->action syntax', FALSE, FALSE);
		$this->registerArgument('localLanguageFileRelativePath', 'string', 'Relative (from extension $extensionName) path to locallang file containing the action method labels', FALSE, '/Resources/Private/Language/locallang_db.xml');
		$this->registerArgument('subActions', 'array', "Array of sub actions {ControllerName: {list: 'update,delete'}, OtherController: {new: 'create'}} which are also allowed but not presented as options when the mapped action is selected (in example: if ControllerName->list is selected, ControllerName->update and ControllerName->delete are allowed - but cannot be selected).", FALSE, array());
		$this->registerArgument('separator', 'string', 'Separator string (glue) for Controller->action values, defaults to "->". Empty values result in default being used.', FALSE, NULL);
	}

	/**
	 * @return ControllerActions
	 * @throws \RuntimeException
	 */
	public function getComponent() {
		$extensionName = $this->arguments['extensionName'];
		$pluginName = $this->arguments['pluginName'];
		$actions = $this->arguments['actions'];
		$controllerName = $this->arguments['controllerName'];
		$separator = $this->arguments['separator'];
		$controllerContext = $this->renderingContext->getControllerContext();
		if (TRUE === $actions instanceof \Traversable) {
			$actions = iterator_to_array($actions);
		}
		if (NULL !== $controllerContext) {
			if (TRUE === empty($extensionName)) {
				$request = $controllerContext->getRequest();
				$extensionName = $this->getFullExtensionNameFromRequest($request);
			}
			if (TRUE === empty($pluginName)) {
				$pluginName = $controllerContext->getRequest()->getPluginName();
			}
		}
		if (TRUE === empty($extensionName) && TRUE === empty($pluginName) && 1 > count($actions)) {
			throw new \RuntimeException('Either "actions", or both "extensionName" and "pluginName" must be used on ' .
				'flux:field.controllerActions. None were found and none were detected from the ControllerContext Request.', 1346514748);
		}
		/** @var ControllerActions $component */
		$component = $this->getPreparedComponent('ControllerActions');
		$component->setItems($this->arguments['items']);
		$component->setExtensionName($extensionName);
		$component->setPluginName($pluginName);
		$component->setControllerName($controllerName);
		$component->setActions($actions);
		$component->setExcludeActions($this->arguments['excludeActions']);
		$component->setPrefixOnRequiredArguments($this->arguments['prefixOnRequiredArguments']);
		$component->setDisableLocalLanguageLabels($this->arguments['disableLocalLanguageLabels']);
		$component->setLocalLanguageFileRelativePath($this->arguments['localLanguageFileRelativePath']);
		$component->setSubActions($this->arguments['subActions']);
		if (FALSE === empty($separator)) {
			$component->setSeparator($separator);
		}
		return $component;
	}

	/**
	 * @param Request $request
	 * @return string
	 */
	protected function getFullExtensionNameFromRequest(Request $request) {
		$vendorName = NULL;
		if (TRUE === method_exists($request, 'getControllerVendorName')) {
			$vendorName = $request->getControllerVendorName();
		}
		$extensionName = $request->getControllerExtensionName();
		if (NULL !== $vendorName) {
			$extensionName = $vendorName . '.' . $extensionName;
		}
		return $extensionName;
	}

}
