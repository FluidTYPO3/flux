<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ControllerActions extends Select
{
    /**
     * Name of the Extbase extension that contains the Controller
     * to parse, ex. MyExtension. In vendor based extensions use
     * dot, ex. Vendor.MyExtension.
     */
    protected string $controllerExtensionName = '';

    /**
     * Name of the Extbase plugin that contains Controller
     * definitions to parse, ex. MyPluginName.
     */
    protected string $pluginName = '';

    /**
     * Optional extra limiting of actions displayed - if used,
     * field only displays actions for this controller name - ex
     * Article(Controller) or FrontendUser(Controller) - the
     * Controller part is implied.
     */
    protected string $controllerName = '';

    /**
     * Array of "ControllerName" => "csv,of,actions" which are
     * allowed. If used, does not require the use of an
     * ExtensionName and PluginName (will use the one specified
     * in your current plugin automatically).
     */
    protected array $actions = [];

    /**
     * Array of "ControllerName" => "csv,of,actions" which must
     * be excluded.
     */
    protected array $excludeActions = [];

    /**
     * A short string denoting that the method takes arguments,
     * ex * (which should then be explained in the documentation
     * for your extension about how to setup your plugins.
     */
    protected string $prefixOnRequiredArguments = '*';

    /**
     * Separator for non-LLL action labels which have no manual
     * label set.
     */
    protected string $separator = '->';

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
     */
    protected array $subActions = [];

    /**
     * Overridden getter: the name of a SwitchableControllerActions
     * field is enforced - the TYPO3 core depends on this name.
     */
    public function getName(): string
    {
        return 'switchableControllerActions';
    }

    /**
     * Overridden setter: ignores any attempt to set another name
     * for this field.
     */
    public function setName(string $name): self
    {
        // intentional intermediate; avoids "unused argument"
        $name = 'switchableControllerActions';
        $this->name = $name;
        return $this;
    }

    public function setActions(array $actions): self
    {
        $this->actions = $actions;
        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setControllerName(string $controllerName): self
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    public function setExcludeActions(array $excludeActions): self
    {
        $this->excludeActions = $excludeActions;
        return $this;
    }

    public function getExcludeActions(): array
    {
        return $this->excludeActions;
    }

    public function setControllerExtensionName(string $extensionName): self
    {
        $this->controllerExtensionName = $extensionName;
        return $this;
    }

    public function getControllerExtensionName(): string
    {
        return $this->controllerExtensionName;
    }

    public function setPluginName(string $pluginName): self
    {
        $this->pluginName = $pluginName;
        return $this;
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setPrefixOnRequiredArguments(string $prefixOnRequiredArguments): self
    {
        $this->prefixOnRequiredArguments = $prefixOnRequiredArguments;
        return $this;
    }

    public function getPrefixOnRequiredArguments(): string
    {
        return $this->prefixOnRequiredArguments;
    }

    public function setSubActions(array $subActions): self
    {
        $this->subActions = $subActions;
        return $this;
    }

    public function getSubActions(): array
    {
        return $this->subActions;
    }

    public function getItems(): array
    {
        $basicItems = parent::getItems();
        if (0 < count($basicItems)) {
            return $basicItems;
        } else {
            $actions = $this->getActions();
            if (0 === count($actions)) {
                $actions = $this->getActionsForExtensionNameAndPluginName();
            }
            return $this->buildItemsForActions($actions);
        }
    }

    protected function getActionsForExtensionNameAndPluginName(): array
    {
        $extensionName = $this->getControllerExtensionName();
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionName);
        $pluginName = $this->getPluginName();
        $actions = (array) ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']
            [$extensionName]['plugins'][$pluginName]['controllers'] ?? []);
        foreach ($actions as $controllerName => $definitions) {
            $actions[$controllerName] = $definitions['actions'];
        }
        return $actions;
    }

    protected function buildExpectedAndExistingControllerClassName(string $controllerName): ?string
    {
        $extensionName = $this->getControllerExtensionName();
        [$vendorName, $extensionName] = ExtensionNamingUtility::getVendorNameAndExtensionName($extensionName);
        if (class_exists($controllerName)) {
            $controllerClassName = $controllerName;
        } elseif (null !== $vendorName) {
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
        if (!class_exists($controllerClassName)) {
            $controllerClassName = null;
        }
        return $controllerClassName;
    }

    protected function getLabelForControllerAction(string $controllerName, string $actionName): string
    {
        $localLanguageFileRelativePath = $this->getLocalLanguageFileRelativePath();
        $extensionName = $this->getControllerExtensionName();
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
        $pluginName = $this->getPluginName();
        $separator = $this->getSeparator();
        $controllerClassName = $this->buildExpectedAndExistingControllerClassName($controllerName);
        if ($controllerClassName === null) {
            return 'INVALID: ' . $controllerName . '->' . $actionName;
        }
        $disableLocalLanguageLabels = $this->getDisableLocalLanguageLabels();
        $labelPath = strtolower($pluginName . '.' . $controllerName . '.' . $actionName);
        $hasLocalLanguageFile = file_exists(
            $this->resolvePathToFileInExtension($extensionKey, $localLanguageFileRelativePath)
        );
        $label = $actionName . $separator . $controllerName;
        if (false === $disableLocalLanguageLabels && true === $hasLocalLanguageFile) {
            $label = 'LLL:EXT:' . $extensionKey . $localLanguageFileRelativePath . ':' . $labelPath;
        } elseif (method_exists($controllerClassName, $actionName . 'Action') && true === $disableLocalLanguageLabels) {
            $methodReflection = $this->reflectAction($controllerName, $actionName);
            $parts = explode("\n", trim((string) $methodReflection->getDocComment(), "/*\n"));
            $line = array_shift($parts);
            $line = trim(trim($line), '* ');
            if (substr($line, 0, 1) !== '@') {
                $label = $line;
            }
        }
        return $label;
    }

    protected function reflectAction(string $controllerName, string $actionName): \ReflectionMethod
    {
        /** @var class-string $controllerClassName */
        $controllerClassName = $this->buildExpectedAndExistingControllerClassName($controllerName);
        $controllerClassReflection = new \ReflectionClass($controllerClassName);
        /** @var \ReflectionMethod $methodReflection */
        $methodReflection = $controllerClassReflection->getMethod($actionName . 'Action');
        return $methodReflection;
    }

    protected function prefixLabel(string $controllerName, string $actionName, string $label): string
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
     */
    protected function convertActionListToArray($actionList): array
    {
        if (is_scalar($actionList)) {
            return GeneralUtility::trimExplode(',', (string) $actionList, true);
        }
        return (array) $actionList;
    }

    /**
     * Renders the TCA-style items array based on the Extbase FlexForm-style
     * definitions of selectable actions (specified manually or read based on
     * ViewHelper arguments).
     */
    protected function buildItemsForActions(array $actions): array
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
                if (in_array($actionName, $exclusions[$controllerName] ?? [])) {
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

    /**
     * @codeCoverageIgnore
     */
    protected function resolvePathToFileInExtension(string $extensionKey, string $path): string
    {
        return ExtensionManagementUtility::extPath($extensionKey, $path);
    }
}
