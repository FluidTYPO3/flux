<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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
 * ************************************************************* */

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
abstract class Tx_Flux_ViewHelpers_AbstractViewHelperTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @return string
	 */
	protected function getViewHelperClassName() {
		$class = get_class($this);
		return substr($class, 0, -4);
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface
	 */
	protected function createNode($type, $value) {
		/** @var Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface $node */
		$className = 'Tx_Fluid_Core_Parser_SyntaxTree_' . $type . 'Node';
		$node = new $className($value);
		return $node;
	}

	/**
	 * @return Tx_Fluid_Core_ViewHelper_AbstractViewHelper
	 */
	protected function createInstance() {
		$className = $this->getViewHelperClassName();
		/** @var Tx_Fluid_Core_ViewHelper_AbstractViewHelper $instance */
		$instance = $this->objectManager->get($className);
		if (TRUE === method_exists($instance, 'injectConfigurationManager')) {
			$cObject = new tslib_cObj();
			$cObject->start(Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren, 'tt_content');
			/** @var Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager */
			$configurationManager = $this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManagerInterface');
			$configurationManager->setContentObject($cObject);
			$instance->injectConfigurationManager($configurationManager);
		}
		return $instance;
	}

	/**
	 * @param array $arguments
	 * @param array $variables
	 * @param Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface $childNode
	 * @return Tx_Fluid_Core_ViewHelper_TemplateVariableContainer
	 */
	protected function executeViewHelper($arguments = array(), $variables = array(), $childNode = NULL) {
		$instance = $this->createInstance();
		/** @var Tx_Fluid_Core_ViewHelper_TemplateVariableContainer $container */
		$container = $this->objectManager->get('Tx_Fluid_Core_ViewHelper_TemplateVariableContainer');
		/** @var Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer $viewHelperContainer */
		$viewHelperContainer = $this->objectManager->get('Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer');
		foreach ($variables as $name => $value) {
			$container->add($name, $value);
		}
		$node = new Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode($instance, $arguments);
		/** @var Tx_Extbase_MVC_Web_Routing_UriBuilder $uriBuilder */
		$uriBuilder = $this->objectManager->get('Tx_Extbase_MVC_Web_Routing_UriBuilder');
		/** @var Tx_Extbase_Mvc_Web_Request $request */
		$request = $this->objectManager->get('Tx_Extbase_Mvc_Web_Request');
		/** @var Tx_Extbase_Mvc_Web_Response $response */
		$response = $this->objectManager->get('Tx_Extbase_Mvc_Web_Response');
		/** @var Tx_Extbase_MVC_Controller_ControllerContext $controllerContext */
		$controllerContext = $this->objectManager->get('Tx_Extbase_MVC_Controller_ControllerContext');
		$controllerContext->setRequest($request);
		$controllerContext->setResponse($response);
		$controllerContext->setUriBuilder($uriBuilder);
		/** @var Tx_Fluid_Core_Rendering_RenderingContext $renderingContext */
		$renderingContext = $this->getAccessibleMock('Tx_Fluid_Core_Rendering_RenderingContext');
		$renderingContext->setControllerContext($controllerContext);
		$renderingContext->injectTemplateVariableContainer($container);
		$renderingContext->injectViewHelperVariableContainer($viewHelperContainer);
		$instance->setArguments($arguments);
		$instance->setRenderingContext($renderingContext);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'templateVariableContainer', $container, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'viewHelperVariableContainer', $viewHelperContainer, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'controllerContext', $controllerContext, TRUE);
		if (TRUE === $instance instanceof Tx_Fluidwidget_Core_Widget_AbstractWidgetViewHelper) {
			/** @var Tx_Fluid_Core_Widget_WidgetContext $widgetContext */
			$widgetContext = $this->objectManager->get('Tx_Fluid_Core_Widget_WidgetContext');
			Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'widgetContext', $widgetContext, TRUE);
		}
		if (NULL !== $childNode) {
			$node->addChildNode($childNode);
			if ($instance instanceof Tx_Fluid_Core_ViewHelper_Facets_ChildNodeAccessInterface) {
				$instance->setChildNodes(array($childNode));
			}
		}
		$instance->setViewHelperNode($node);
		$output = $instance->initializeArgumentsAndRender();
		return $output;
	}

}
