<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RequestBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Response;

/**
 * Pipe: Controller Action
 *
 * Passes data through a controller action
 */
class ControllerPipe extends AbstractPipe implements PipeInterface
{
    protected string $controller = '';
    protected string $action = '';
    protected string $extensionName = '';

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
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionName);

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = GeneralUtility::makeInstance(RequestBuilder::class);
        $request = $requestBuilder->buildRequestFor(
            $extensionName,
            $this->getController(),
            $this->getAction(),
            '',
            $data
        );

        /** @var Response $response */
        $response = GeneralUtility::makeInstance(Response::class);
        /** @var Dispatcher $dispatcher */
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        /** @var RequestInterface $request */
        $dispatcher->dispatch($request, $response);
        return $response->getContent();
    }
}
