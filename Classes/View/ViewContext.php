<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Class ViewContext
 *
 * @deprecated To be removed in next major release
 */
class ViewContext
{

    /**
     * @var string
     */
    protected $packageName;

    /**
     * @var string
     */
    protected $sectionName;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var TemplatePaths
     */
    protected $templatePaths;

    /**
     * @var string
     */
    protected $templatePathAndFilename;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param string $templatePathAndFilename
     * @param string $packageName
     * @param string $controllerName
     * @param RequestInterface|NULL $request
     */
    public function __construct(
        $templatePathAndFilename = null,
        $packageName = null,
        $controllerName = null,
        RequestInterface $request = null
    ) {
        if (true === $request instanceof RequestInterface) {
            $this->request = clone $request;
        } else {
            $this->request = new Request();
            $this->request->setFormat(TemplatePaths::DEFAULT_FORMAT);
            $this->request->setControllerName($controllerName);
            $this->request->setControllerActionName(lcfirst(pathinfo($templatePathAndFilename, PATHINFO_FILENAME)));
        }
        $this->setTemplatePathAndFilename($templatePathAndFilename);
        $this->setTemplatePaths(new TemplatePaths($packageName));
        if (null !== $packageName) {
            $this->setPackageName($packageName);
        }
        if (null !== $controllerName) {
            $this->setControllerName($controllerName);
        }
    }

    /**
     * @return string
     */
    public function getExtensionKey()
    {
        return ExtensionNamingUtility::getExtensionKey($this->packageName);
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return ExtensionNamingUtility::getExtensionName($this->packageName);
    }

    /**
     * @return string
     */
    public function getVendorName()
    {
        return ExtensionNamingUtility::getVendorName($this->packageName);
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @param string $packageName
     * @return void
     */
    public function setPackageName($packageName)
    {
        $this->packageName = $packageName;
        list ($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName($packageName);
        $this->request->setControllerVendorName($vendorName);
        $this->request->setControllerExtensionName($extensionName);
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->request->getControllerName();
    }

    /**
     * @param string $controllerName
     * @return void
     */
    public function setControllerName($controllerName)
    {
        $this->request->setControllerName($controllerName);
    }

    /**
     * @return string
     */
    public function getSectionName()
    {
        return $this->sectionName;
    }

    /**
     * @param string $sectionName
     * @return void
     */
    public function setSectionName($sectionName)
    {
        $this->sectionName = $sectionName;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     * @return void
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * @return TemplatePaths
     */
    public function getTemplatePaths()
    {
        return $this->templatePaths;
    }

    /**
     * @param TemplatePaths $templatePaths
     */
    public function setTemplatePaths(TemplatePaths $templatePaths)
    {
        $this->templatePaths = $templatePaths;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->request->getFormat();
    }

    /**
     * @param string $format
     * @return void
     */
    public function setFormat($format)
    {
        $this->request->setFormat($format);
    }

    /**
     * @return string
     */
    public function getTemplatePathAndFilename()
    {
        return $this->templatePathAndFilename;
    }

    /**
     * @param string $templatePathAndFilename
     * @return void
     */
    public function setTemplatePathAndFilename($templatePathAndFilename)
    {
        $this->templatePathAndFilename = $templatePathAndFilename;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     * @return void
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        // Removes the form and grid objects from $variables
        // This prevents avoiding errors during serialization with \Closure objects in the form or grid objects
        // Also they are not necessary to create a unique hash
        $variables = $this->variables;
        unset($variables['form'], $variables['grid'] );
        return sha1(
            $this->packageName .
            $this->templatePathAndFilename .
            serialize($variables) .
            serialize($this->templatePaths)
        );
    }
}
