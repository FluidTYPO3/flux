<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ControllerActions
 */
class ControllerActions extends Select
{

    /**
     * Name of the Extbase extension that contains the Controller
     * to parse, ex. MyExtension. In vendor based extensions use
     * dot, ex. Vendor.MyExtension.
     *
     * @var string
     */
    protected $controllerExtensionName;

    /**
     * Name of the Extbase plugin that contains Controller
     * definitions to parse, ex. MyPluginName.
     *
     * @var string
     */
    protected $pluginName;

    /**
     * Optional extra limiting of actions displayed - if used,
     * field only displays actions for this controller name - ex
     * Article(Controller) or FrontendUser(Controller) - the
     * Controller part is implied.
     *
     * @var string
     */
    protected $controllerName;

    /**
     * Array of "ControllerName" => "csv,of,actions" which are
     * allowed. If used, does not require the use of an
     * ExtensionName and PluginName (will use the one specified
     * in your current plugin automatically).
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Array of "ControllerName" => "csv,of,actions" which must
     * be excluded.
     *
     * @var array
     */
    protected $excludeActions = [];

    /**
     * A short string denoting that the method takes arguments,
     * ex * (which should then be explained in the documentation
     * for your extension about how to setup your plugins.
     *
     * @var string
     */
    protected $prefixOnRequiredArguments = '*';

    /**
     * Separator for non-LLL action labels which have no manual
     * label set.
     *
     * @var string
     */
    protected $separator = '->';

    /**
     * Array of also allowed actions which will be allowed when
     * a particular Controller+action is selected - but will not
     * be displayed as selectable options.
     *
     * Example: defining an array such as this one:
     *
     * [
     *      'News' => [
     *          'list' => 'update,delete
     *      ),
     *      'Category' => [
     *          'new' => 'create',
     *          'list' => 'filter,search'
     *      )
     * );
     *
     * Indicates that whenever the selector field is set to use
     * the "News->list" SwitchableControllerAction, Extbase will
     * in addition also allow the "News->update" and "News->delete"
     * sub-actions, but will not display them. And when the value
     * is "Category->new", Extbase will also allow "Category->create",
     * and finally when value is "Category->list" the additional
     * actions "Category->filter" and "Category->search" are allowed
     * but not displayed as select options.
     *
     * This behavior can be compared to small "sub-plugins"; regular
     * Extbase plugins will have a default action and additional
     * allowed "Controller->action" combinations - whereas this
     * method uses the selected value as a sort of "default" action
     * and uses these sub actions much the same way an Extbase plugin
     * would use the not-default actions configured in a plugin.
     *
     * Use of this feature is necessary if one or more of your
     * SwitchableControllerAction selection values must allow more
     * than one action to be used (which would be the case in for
     * example a new/create, edit/update pair of actions).
     *
     * @var array
     */
    protected $subActions = [];

    /**
     * Overridden getter: the name of a SwitchableControllerActions
     * field is enforced - the TYPO3 core depends on this name.
     *
     * @return string
     */
    public function getName()
    {
        return 'switchableControllerActions';
    }

    /**
     * Overridden setter: ignores any attempt to set another name
     * for this field.
     *
     * @param string $name
     * @return FormInterface
     */
    public function setName($name)
    {
        // intentional intermediate; avoids "unused argument"
        $name = 'switchableControllerActions';
        $this->name = $name;
        return $this;
    }

    /**
     * @param array $actions
     * @return ControllerActions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param string $controllerName
     * @return ControllerActions
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }
    /**
     * @param array $excludeActions
     * @return ControllerActions
     */
    public function setExcludeActions($excludeActions)
    {
        $this->excludeActions = (array) $excludeActions;
        return $this;
    }

    /**
     * @return array
     */
    public function getExcludeActions()
    {
        return (array) $this->excludeActions;
    }

    /**
     * @param string $extensionName
     * @return ControllerActions
     */
    public function setControllerExtensionName($extensionName)
    {
        $this->controllerExtensionName = $extensionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getControllerExtensionName()
    {
        return $this->controllerExtensionName;
    }

    /**
     * @param string $pluginName
     * @return ControllerActions
     */
    public function setPluginName($pluginName)
    {
        $this->pluginName = $pluginName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * @param string $separator
     * @return ControllerActions
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * @param string $prefixOnRequiredArguments
     * @return ControllerActions
     */
    public function setPrefixOnRequiredArguments($prefixOnRequiredArguments)
    {
        $this->prefixOnRequiredArguments = $prefixOnRequiredArguments;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefixOnRequiredArguments()
    {
        return $this->prefixOnRequiredArguments;
    }

    /**
     * @param array $subActions
     * @return ControllerActions
     */
    public function setSubActions($subActions)
    {
        $this->subActions = $subActions;
        return $this;
    }

    /**
     * @return array
     */
    public function getSubActions()
    {
        return $this->subActions;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $basicItems = parent::getItems();
        if (0 < count($basicItems)) {
            return $basicItems;
        } else {
            $actions = $this->getActions();
            if (0 === count($actions)) {
                $actions = $this->getActionsForExtensionNameAndPluginName(
                    $this->controllerExtensionName,
                    $this->pluginName
                );
            }
            return $this->buildItemsForActions($actions);
        }
    }

    /**
     * Reads a list of allowed actions for $extensionName's plugin $pluginName
     *
     * @return array
     */
    protected function getActionsForExtensionNameAndPluginName()
    {
        $extensionName = $this->getControllerExtensionName();
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionName);
        $pluginName = $this->getPluginName();
        $actions = (array) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']
            [$extensionName]['plugins'][$pluginName]['controllers'];
        foreach ($actions as $controllerName => $definitions) {
            $actions[$controllerName] = $definitions['actions'];
        }
        return $actions;
    }

    /**
     * @param string $controllerName
     * @return string|NULL
     */
    protected function buildExpectedAndExistingControllerClassName($controllerName)
    {
        $extensionName = $this->getControllerExtensionName();
        list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName($extensionName);
        if (null !== $vendorName) {
            $controllerClassName = sprintf(
                '%s\\%s\\Controller\\%sController',
                $vendorName,
                $extensionName,
                $controllerName
            );
        } else {
            $controllerClassName = $extensionName . '\\Controller\\' . $controllerName . 'Controller';
            if (false === class_exists($controllerClassName)) {
                $controllerClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerName . 'Controller';
            }
        }
        if (false === class_exists($controllerClassName)) {
            $controllerClassName = null;
        }
        return $controllerClassName;
    }

    /**
     * @param string $controllerName
     * @param string $actionName
     * @return string|NULL
     */
    protected function getLabelForControllerAction($controllerName, $actionName)
    {
        $localLanguageFileRelativePath = $this->getLocalLanguageFileRelativePath();
        $extensionName = $this->getControllerExtensionName();
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
        $pluginName = $this->getPluginName();
        $separator = $this->getSeparator();
        $controllerClassName = $this->buildExpectedAndExistingControllerClassName($controllerName);
        $disableLocalLanguageLabels = $this->getDisableLocalLanguageLabels();
        $labelPath = strtolower($pluginName . '.' . $controllerName . '.' . $actionName);
        $hasLocalLanguageFile = file_exists(
            ExtensionManagementUtility::extPath($extensionKey, $localLanguageFileRelativePath)
        );
        $label = $actionName . $separator . $controllerName;
        if (false === $disableLocalLanguageLabels && true === $hasLocalLanguageFile) {
            $label = 'LLL:EXT:' . $extensionKey . $localLanguageFileRelativePath . ':' . $labelPath;
        } elseif (method_exists($controllerClassName, $actionName . 'Action') && true === $disableLocalLanguageLabels) {
            $methodReflection = $this->reflectAction($controllerName, $actionName);
            $line = array_shift(explode("\n", trim($methodReflection->getDocComment(), "/*\n")));
            $line = trim(trim($line), '* ');
            if (substr($line, 0, 1) !== '@') {
                $label = $line;
            }
        }
        return $label;
    }

    /**
     * @param string $controllerName
     * @param string $actionName
     * @return \ReflectionMethod
     */
    protected function reflectAction($controllerName, $actionName)
    {
        $controllerClassName = $this->buildExpectedAndExistingControllerClassName($controllerName);
        /** @var \ReflectionMethod $methodReflection */
        $controllerClassReflection = new \ReflectionClass($controllerClassName);
        $methodReflection = $controllerClassReflection->getMethod($actionName . 'Action');
        return $methodReflection;
    }


    /**
     * @param string $controllerName
     * @param string $actionName
     * @param string $label
     * @return string
     */
    protected function prefixLabel($controllerName, $actionName, $label)
    {
        $controllerClassName = $this->buildExpectedAndExistingControllerClassName($controllerName);
        if (null === $controllerClassName || false === method_exists($controllerClassName, $actionName . 'Action')) {
            return $label;
        }
        $methodReflection = $this->reflectAction($controllerName, $actionName);
        $hasRequiredArguments = (boolean) ($methodReflection->getNumberOfRequiredParameters() > 0);
        $prefixOnRequiredArguments = $this->getPrefixOnRequiredArguments();
        $prefix = !empty($prefixOnRequiredArguments) && $hasRequiredArguments ? $prefixOnRequiredArguments : null;
        if (null !== $prefix) {
            $label = $prefix . ' ' . $label;
        }
        return $label;
    }

    /**
     * @param mixed $actionList
     * @return array
     */
    protected function convertActionListToArray($actionList)
    {
        if (false === is_array($actionList)) {
            return GeneralUtility::trimExplode(',', $actionList, true);
        }
        return $actionList;
    }

    /**
     * Renders the TCA-style items array based on the Extbase FlexForm-style
     * definitions of selectable actions (specified manually or read based on
     * ViewHelper arguments)
     *
     * @param array $actions
     * @return array
     */
    protected function buildItemsForActions(array $actions)
    {
        $separator = $this->getSeparator();
        $subActions = $this->getSubActions();
        $exclusions = $this->getExcludeActions();
        foreach ($exclusions as $controllerName => $controllerActionList) {
            $exclusions[$controllerName] = $this->convertActionListToArray($controllerActionList);
        }
        $items = [];
        $limitByControllerName = $this->getControllerName();
        foreach ($actions as $controllerName => $controllerActionList) {
            $controllerActions = $this->convertActionListToArray($controllerActionList);
            $controllerClassName = $this->buildExpectedAndExistingControllerClassName($controllerName);
            if (null === $controllerClassName) {
                continue;
            }
            foreach ($controllerActions as $actionName) {
                if (is_array($exclusions[$controllerName]) && in_array($actionName, $exclusions[$controllerName])) {
                    continue;
                } elseif ($limitByControllerName && $controllerName !== $limitByControllerName) {
                    continue;
                }
                $label = $this->getLabelForControllerAction($controllerName, $actionName);
                $label = $this->prefixLabel($controllerName, $actionName, $label);
                $actionKey = [$controllerName . $separator . $actionName];
                if (isset($subActions[$controllerName][$actionName])) {
                    $subActionsArray = $this->convertActionListToArray($subActions[$controllerName][$actionName]);
                    foreach ($subActionsArray as $allowedActionName) {
                        $actionKey[] = $controllerName . $separator . $allowedActionName;
                    }
                }
                $values = [
                    $label,
                    implode(';', $actionKey),
                ];
                array_push($items, $values);
            }
        }
        return $items;
    }
}
