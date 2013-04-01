<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * ExposedStandaloneView. Allows access to registered template and viewhelper
 * variables from a Fluid template.
 *
 * @package Flux
 * @subpackage MVC/View
 */
class Tx_Flux_MVC_View_ExposedStandaloneView extends Tx_Fluid_View_StandaloneView {

	/**
	 * @var Tx_Flux_Service_DebugService
	 */
	protected $debugService;

	/**
	 * @param Tx_Flux_Service_DebugService $debugService
	 * @return void
	 */
	public function injectDebugService(Tx_Flux_Service_DebugService $debugService) {
		$this->debugService = $debugService;
	}

	/**
	 * Get a variable stored in the Fluid template
	 * @param string $viewHelperClassName Class name of the ViewHelper which stored the variable
	 * @param string $name Name of the variable which the ViewHelper stored
	 * @param string $sectionName Optional name of a section in which the ViewHelper was called
	 * @param array $paths Template paths; required if template renders Partials (from inside $sectionName, if specified)
	 * @param string $extensionName If specified, overrides the extension name stored in the RenderingContext. Use with care.
	 * @return mixed
	 * @throws Exception
	 */
	public function getStoredVariable($viewHelperClassName, $name, $sectionName = NULL, $paths = NULL, $extensionName = NULL) {
		try {
			if ($this->controllerContext instanceof Tx_Extbase_MVC_Controller_ControllerContext === FALSE) {
				throw new Exception('ExposedStandaloneView->getStoredVariable requires a ControllerContext, none exists', 1343521593);
			}
			if (NULL === $extensionName && TRUE === isset($paths['extensionKey'])) {
				$extensionName = t3lib_div::underscoredToLowerCamelCase($paths['extensionKey']);
			}
			if (NULL !== $extensionName) {
				$request = $this->controllerContext->getRequest();
				$request->setControllerExtensionName($extensionName);
				$this->controllerContext->setRequest($request);
				$this->controllerContext->setRequest($request);
			}
			$this->baseRenderingContext->setControllerContext($this->controllerContext);
			$value = NULL;
			if (is_array($paths)) {
				$this->setPartialRootPath($paths['partialRootPath']);
				$this->setLayoutRootPath($paths['layoutRootPath']);
			}
			$this->templateParser->setConfiguration($this->buildParserConfiguration());
			$parsedTemplate = $this->getParsedTemplate();
			if (NULL === $parsedTemplate) {
				throw new Exception('Unable to fetch a parsed template - this is <b>very likely</b> to be caused by ' .
					' syntax errors in the template. It may also point to a problem in a core class from Fluid; however, ' .
					' this is <b>not very likely</b> to be the cause. There almost certainly are earlier errors which should ' .
					' be handled; if there are then you can safely ignore this message.', t3lib_div::SYSLOG_SEVERITY_WARNING);
			}
			$this->startRendering(Tx_Fluid_View_AbstractTemplateView::RENDERING_TEMPLATE, $parsedTemplate, $this->baseRenderingContext);
			if (FALSE === empty($sectionName)) {
				$this->renderSection($sectionName, $this->baseRenderingContext->getTemplateVariableContainer()->getAll());
			} else {
				$this->render();
			}
			$this->stopRendering();
			$stored = $this->baseRenderingContext->getViewHelperVariableContainer()->get($viewHelperClassName, $name);
			$this->debugService->message('Flux View ' . get_class($this) . ' is able to read stored configuration from file ' .
				$this->getTemplatePathAndFilename(), t3lib_div::SYSLOG_SEVERITY_INFO);
			return $stored;
		} catch (Exception $error) {
			$this->debugService->message('Failed to get stored variable from file ' . $this->getTemplatePathAndFilename() . ' - ' .
				'additional error messages have been sent', t3lib_div::SYSLOG_SEVERITY_FATAL);
			$this->debugService->debug($error);
			$value = NULL;
		}
		return $value;
	}

	/**
	 * Get a parsed syntax tree for this current template
	 * @return mixed
	 */
	public function getParsedTemplate() {
		try {
			if (!$this->templateCompiler) {
				$source = $this->getTemplateSource();
				$parsedTemplate = $this->templateParser->parse($source);
				return $parsedTemplate;
			} else {
				$templateIdentifier = $this->getTemplateIdentifier();
				if ($this->templateCompiler->has($templateIdentifier)) {
					$parsedTemplate = $this->templateCompiler->get($templateIdentifier);
				} else {
					$source = $this->getTemplateSource();
					$parsedTemplate = $this->templateParser->parse($source);
					if ($parsedTemplate->isCompilable()) {
						$this->templateCompiler->store($templateIdentifier, $parsedTemplate);
					}
				}
				return $parsedTemplate;
			}
		} catch (Exception $error) {
			$this->debugService->message('Failed to parse Fluid template in file ' . $this->getTemplatePathAndFilename() . ' - ' .
				'additional error messages have been sent', t3lib_div::SYSLOG_SEVERITY_FATAL);
			$this->debugService->debug($error);
			return NULL;
		}
	}

	/**
	 * Exposition proxy for startRendering() method
	 *
	 * @param integer $type
	 * @param Tx_Fluid_Core_Parser_ParsedTemplateInterface $parsedTemplate
	 * @param Tx_Fluid_Core_Rendering_RenderingContextInterface $renderingContext
	 */
	public function startRendering($type, Tx_Fluid_Core_Parser_ParsedTemplateInterface $parsedTemplate, Tx_Fluid_Core_Rendering_RenderingContextInterface $renderingContext) {
		try {
			parent::startRendering($type, $parsedTemplate, $renderingContext);
		} catch (Exception $error) {
			$this->debugService->debug($error);
		}
	}

	/**
	 * Exposition proxy for stopRendering() method
	 *
	 * @return void
	 */
	public function stopRendering() {
		try {
			parent::stopRendering();
		} catch (Exception $error) {
			$this->debugService->debug($error);
		}
	}

	/**
	 * Renders a section from the specified template w/o requring a call to the
	 * main render() method - allows for cherry-picking sections to render.
	 * @param string $sectionName
	 * @param array $variables
	 * @param boolean $optional
	 * @return string
	 */
	public function renderStandaloneSection($sectionName, $variables, $optional=TRUE) {
		$content = NULL;
		try {
			$this->startRendering(Tx_Fluid_View_AbstractTemplateView::RENDERING_TEMPLATE, $this->getParsedTemplate(), $this->baseRenderingContext);
			$content = $this->renderSection($sectionName, $variables, $optional);
			$this->stopRendering();
		} catch (Exception $error) {
			$this->debugService->message('Failed to render section "' . $sectionName .'" in file ' . $this->getTemplatePathAndFilename() .
				' - see next error message', t3lib_div::SYSLOG_SEVERITY_FATAL);
			$this->debugService->debug($error);
		}
		return $content;
	}

}
