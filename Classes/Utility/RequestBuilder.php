<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

class RequestBuilder
{
    public function buildRequestFor(
        string $extensionIdentity,
        string $controllerName,
        string $controllerActionName,
        string $pluginName,
        array $arguments = []
    ): RequestInterface {
        /** @var ServerRequest $serverRequest */
        $serverRequest = $this->getServerRequest();

        $controllerExtensionName = ExtensionNamingUtility::getExtensionName($extensionIdentity);
        if (class_exists(ExtbaseRequestParameters::class)) {
            $expectedControllerClassName = $this->buildControllerClassName($extensionIdentity, $controllerName);
            $extbaseQueryParameters = new ExtbaseRequestParameters($expectedControllerClassName);
            $extbaseQueryParameters->setControllerExtensionName($controllerExtensionName);
            $extbaseQueryParameters->setControllerName($controllerName);
            $extbaseQueryParameters->setControllerActionName($controllerActionName);
            $extbaseQueryParameters->setPluginName($pluginName);
            $extbaseQueryParameters->setArguments($arguments);
            /** @var Request $request */
            $request = GeneralUtility::makeInstance(
                Request::class,
                $serverRequest->withAttribute('extbase', $extbaseQueryParameters)
            );
        } else {
            /** @var Request $request */
            $request = GeneralUtility::makeInstance(Request::class);
            if (method_exists($request, 'setFormat')) {
                $request->setFormat('html');
            }
            if (method_exists($request, 'setControllerName')) {
                $request->setControllerName($controllerName);
            }
            if (method_exists($request, 'setControllerExtensionName')) {
                $request->setControllerExtensionName($controllerExtensionName);
            }
            if (method_exists($request, 'setControllerActionName')) {
                $request->setControllerActionName($controllerActionName);
            }
            if (method_exists($request, 'setArguments')) {
                $request->setArguments($arguments);
            }
        }

        if (method_exists($request, 'setRequestUri')) {
            $request->setRequestUri($this->getEnvironmentVariable('TYPO3_REQUEST_URL'));
        }
        if (method_exists($request, 'setBaseUri')) {
            $request->setBaseUri($this->getEnvironmentVariable('TYPO3_SITE_URL'));
        }

        return $request;
    }

    private function getServerRequest(): ?ServerRequest
    {
        /** @var ServerRequest|null $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        return $request;
    }

    private function buildControllerClassName(string $extensionIdentity, string $controllerName): string
    {
        $controllerExtensionName = ExtensionNamingUtility::getExtensionName($extensionIdentity);
        $controllerVendorName = ExtensionNamingUtility::getVendorName($extensionIdentity);
        $expectedControllerClassName = sprintf(
            '%s\\%s\\Controller\\%sController',
            $controllerVendorName,
            $controllerExtensionName,
            $controllerName
        );
        if (!class_exists($expectedControllerClassName)) {
            $expectedControllerClassName = sprintf(
                '%s\\%s\\Controller\\%sController',
                'FluidTYPO3',
                'Flux',
                $controllerName
            );
        }
        return $expectedControllerClassName;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getEnvironmentVariable(string $name): string
    {
        $returnValue = GeneralUtility::getIndpEnv($name);
        if (!is_scalar($returnValue)) {
            return '';
        }
        return $returnValue ? (string) $returnValue : '';
    }
}
