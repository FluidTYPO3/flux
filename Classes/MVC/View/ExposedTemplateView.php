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
 * ExposedTemplateView. Allows access to registered template and viewhelper
 * variables from a Fluid template.
 *
 * @package Flux
 * @subpackage MVC/View
 */
class Tx_Flux_MVC_View_ExposedTemplateView extends Tx_Fluid_View_TemplateView {

	/**
	 * Get a variable stored in the Fluid template
	 * @param string $viewHelperClassName Class name of the ViewHelper which stored the variable
	 * @param string $name Name of the variable which the ViewHelper stored
	 * @param string $sectionName Optional name of a section in which the ViewHelper was called
	 * @return mixed
	 * @throws Exception
	 */
	public function getStoredVariable($viewHelperClassName, $name, $sectionName = NULL) {
		if ($this->controllerContext instanceof Tx_Extbase_MVC_Controller_ControllerContext === FALSE) {
			throw new Exception('ExposedTemplateView->getStoredVariable requires a ControllerContext, none exists', 1343521593);
		}
		$this->templateParser->setConfiguration($this->buildParserConfiguration());
		$this->baseRenderingContext->setControllerContext($this->controllerContext);
		$this->setRenderingContext($this->baseRenderingContext);
		$this->startRendering(Tx_Fluid_View_AbstractTemplateView::RENDERING_TEMPLATE, $this->getParsedTemplate(), $this->baseRenderingContext);
		if ($sectionName !== NULL) {
			$this->renderSection($sectionName, $this->baseRenderingContext->getTemplateVariableContainer()->getAll());
		} else {
			$this->render();
		}
		$this->stopRendering();
		return $this->baseRenderingContext->getViewHelperVariableContainer()->get($viewHelperClassName, $name);
	}

	/**
	 * Get a parsed syntax tree for this current template
	 * @return mixed
	 */
	public function getParsedTemplate() {
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
		$this->baseRenderingContext->setControllerContext($this->controllerContext);
		$this->startRendering(Tx_Fluid_View_AbstractTemplateView::RENDERING_TEMPLATE, $this->getParsedTemplate(), $this->baseRenderingContext);
		$content = $this->renderSection($sectionName, $variables, $optional);
		$this->stopRendering();
		return $content;
	}

}
