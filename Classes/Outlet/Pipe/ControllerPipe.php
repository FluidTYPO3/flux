<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Pipe: Controller Action
 *
 * Passes data through a controller action
 */
class ControllerPipe extends AbstractPipe implements PipeInterface
{
    protected ObjectManagerInterface $objectManager;

    protected string $controller = '';
    protected string $action = '';
    protected string $extensionName = '';

    public function injectObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setExtensionName(string $extensionName): self
    {
        $this->extensionName = $extensionName;
        return $this;
    }

    public function getExtensionName(): string
    {
        return $this->extensionName;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function conduct($data)
    {
        $extensionName = $this->getExtensionName();
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerName($this->getController());
        $request->setControllerActionName($this->getAction());
        list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName($extensionName);
        $request->setControllerExtensionName($extensionName);
        if (null !== $vendorName && method_exists($request, 'setControllerVendorName')) {
            $request->setControllerVendorName($vendorName);
        }

        $request->setArguments($data);
        /** @var Response $response */
        $response = $this->objectManager->get(Response::class);
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->objectManager->get(Dispatcher::class);
        /** @var RequestInterface $request */
        $dispatcher->dispatch($request, $response);
        return $response->getContent();
    }
}
