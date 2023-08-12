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

class Resolver
{
    /**
     * @return class-string|null
     */
    public function resolveFluxControllerClassNameByExtensionKeyAndControllerName(
        string $extensionKey,
        string $controllerObjectShortName,
        bool $failHard = false
    ): ?string {
        $potentialControllerClassName = $this->buildControllerClassNameFromExtensionKeyAndControllerType(
            $extensionKey,
            $controllerObjectShortName
        );
        if (!class_exists($potentialControllerClassName)) {
            if ($failHard) {
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

    private function buildControllerClassNameFromExtensionKeyAndControllerType(
        string $extensionKey,
        string $controllerName
    ): string {
        if (ExtensionNamingUtility::hasVendorName($extensionKey)) {
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

    public function resolveControllerNameFromControllerClassName(string $controllerClassName): string
    {
        $parts = explode('\\', $controllerClassName);
        $className = array_pop($parts);
        return substr($className, 0, -10);
    }
}
