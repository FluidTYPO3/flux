<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\Select;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Pipe: Controller Action
 *
 * Passes data through a controller action
 */
class ControllerPipe extends AbstractPipe implements PipeInterface
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return FieldInterface[]
     */
    public function getFormFields()
    {
        $fields = parent::getFormFields();
        $extensionNames = array_keys((array) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']);
        $extensionNames = array_combine($extensionNames, $extensionNames);
        $fields['action'] = Input::create(['type' => 'Input']);
        $fields['action']->setName('action');
        $fields['action']->setValidate('trim,required');
        $fields['controller'] = Input::create(['type' => 'Input']);
        $fields['controller']->setName('controller');
        $fields['controller']->setValidate('trim,required');
        /** @var Select $selectField */
        $selectField = Select::create(['type' => 'Select']);
        $selectField->setName('extensionName');
        $selectField->setItems($extensionNames);
        $fields['extensionName'] = $selectField;
        return $fields;
    }

    /**
     * @param string $controller
     * @return ControllerPipe
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $action
     * @return ControllerPipe
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $extensionName
     * @return ControllerPipe
     */
    public function setExtensionName($extensionName)
    {
        $this->extensionName = $extensionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtensionName()
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
        /** @var $request Request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerName($this->getController());
        $request->setControllerActionName($this->getAction());
        list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName($extensionName);
        $request->setControllerExtensionName($extensionName);
        if (null !== $vendorName) {
            $request->setControllerVendorName($vendorName);
        }

        $request->setArguments($data);
        /** @var $response Response */
        $response = $this->objectManager->get(Response::class);
        /** @var $dispatcher Dispatcher */
        $dispatcher = $this->objectManager->get(Dispatcher::class);
        $dispatcher->dispatch($request, $response);
        return $response->getContent();
    }
}
