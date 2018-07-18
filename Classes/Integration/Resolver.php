<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;

/**
 * Class Resolver
 */
class Resolver
{

    /**
     * @param string $extensionKey
     * @param string $controllerObjectShortName
     * @param boolean $failHard
     * @throws \RuntimeException
     * @return string|NULL
     */
    public function resolveFluxControllerClassNameByExtensionKeyAndControllerName(
        $extensionKey,
        $controllerObjectShortName,
        $failHard = false
    ) {
        $potentialControllerClassName = static::buildControllerClassNameFromExtensionKeyAndControllerType(
            $extensionKey,
            $controllerObjectShortName
        );
        if (false === class_exists($potentialControllerClassName)) {
            if (true === $failHard) {
                throw new \RuntimeException(
                    'Class ' . $potentialControllerClassName . ' does not exist. It was build from: ' .
                    var_export($extensionKey, true) . ' but the resulting class name was not found.',
                    1364498093
                );
            }
            $potentialControllerClassName = null;
        }
        return HookHandler::trigger(
            HookHandler::CONTROLLER_RESOLVED,
            [
                'extensionKey' => $extensionKey,
                'controllerName' => $controllerObjectShortName,
                'controllerClassName' => $potentialControllerClassName
            ]
        )['controllerClassName'];
    }

    /**
     * @param string $extensionKey
     * @param string $controllerName
     * @return boolean|string
     */
    private static function buildControllerClassNameFromExtensionKeyAndControllerType($extensionKey, $controllerName)
    {
        if (true === ExtensionNamingUtility::hasVendorName($extensionKey)) {
            list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName($extensionKey);
            $potentialClassName = sprintf(
                '%s\\%s\\Controller\\%sController',
                $vendorName,
                $extensionName,
                $controllerName
            );
        } else {
            $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
            $potentialClassName = $extensionName . '\\Controller\\' . $controllerName . 'Controller';
        }
        return $potentialClassName;
    }
}
