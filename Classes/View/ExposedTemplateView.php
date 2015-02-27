<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3\CMS\Fluid\View\AbstractTemplateView;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * ExposedTemplateView. Allows access to registered template and viewhelper
 * variables from a Fluid template.
 *
 * @package Flux
 * @subpackage MVC/View
 */
class ExposedTemplateView extends TemplateView implements ViewInterface {

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var string
	 */
	protected $templateSource = NULL;

	/**
	 * @var array
	 */
	protected $providerPaths = array();

	/**
	 * @var array
	 */
	protected $renderedSections = array();

	/**
	 * @return array
	 */
	public function getProviderPaths() {
		return $this->providerPaths;
	}

	/**
	 * @param array $providerPaths
	 */
	public function setProviderPaths($providerPaths) {
		$this->providerPaths = $providerPaths;
	}

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectDebugService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param TemplatePaths $templatePaths
	 * @return void
	 */
	public function setTemplatePaths(TemplatePaths $templatePaths) {
		$this->setTemplateRootPaths($templatePaths->getTemplateRootPaths());
		$this->setLayoutRootPaths($templatePaths->getLayoutRootPaths());
		$this->setPartialRootPaths($templatePaths->getPartialRootPaths());
	}

	/**
	 * @param string $sectionName
	 * @param string $formName
	 * @return Form|NULL
	 */
	public function getForm($sectionName = 'Configuration', $formName = 'form') {
		/** @var Form $form */
		$form = $this->getStoredVariable(AbstractFormViewHelper::SCOPE, $formName, $sectionName);
		if (NULL !== $form && TRUE === isset($this->templatePathAndFilename)) {
			$form->setOption(Form::OPTION_TEMPLATEFILE, $this->templatePathAndFilename);
		}
		return $form;
	}

	/**
	 * @param string $sectionName
	 * @param string $gridName
	 * @return Grid
	 */
	public function getGrid($sectionName = 'Configuration', $gridName = 'grid') {
		/** @var Grid[] $grids */
		/** @var Grid $grid */
		$grids = $this->getStoredVariable(AbstractFormViewHelper::SCOPE, 'grids', $sectionName);
		$grid = NULL;
		if (TRUE === isset($grids[$gridName])) {
			$grid = $grids[$gridName];
		}
		return $grid;
	}

	/**
	 * Get a variable stored in the Fluid template
	 * @param string $viewHelperClassName Class name of the ViewHelper which stored the variable
	 * @param string $name Name of the variable which the ViewHelper stored
	 * @param string $sectionName Optional name of a section in which the ViewHelper was called
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function getStoredVariable($viewHelperClassName, $name, $sectionName = NULL) {
		if (TRUE === isset($this->renderedSections[$sectionName])) {
			return $this->renderedSections[$sectionName]->get($viewHelperClassName, $name);
		}
		if (FALSE === $this->controllerContext instanceof ControllerContext) {
			throw new \RuntimeException('ExposedTemplateView->getStoredVariable requires a ControllerContext, none exists (getStoredVariable method)', 1343521593);
		}
		$this->baseRenderingContext->setControllerContext($this->controllerContext);
		$this->templateParser->setConfiguration($this->buildParserConfiguration());
		$parsedTemplate = $this->getParsedTemplate();
		$this->startRendering(AbstractTemplateView::RENDERING_TEMPLATE, $parsedTemplate, $this->baseRenderingContext);
		$viewHelperVariableContainer = $this->baseRenderingContext->getViewHelperVariableContainer();
		if (FALSE === empty($sectionName)) {
			$this->renderSection($sectionName, $this->baseRenderingContext->getTemplateVariableContainer()->getAll());
		} else {
			$this->render();
		}
		$this->stopRendering();
		if (FALSE === $viewHelperVariableContainer->exists($viewHelperClassName, $name)) {
			return NULL;
		}
		$this->renderedSections[$sectionName] = $viewHelperVariableContainer;
		$stored = $viewHelperVariableContainer->get($viewHelperClassName, $name);
		$templateIdentityForLog = NULL !== $this->templateSource ? 'source code with hash value ' . sha1($this->templateSource) : $this->getTemplatePathAndFilename();
		$this->configurationService->message('Flux View ' . get_class($this) . ' is able to read stored configuration from ' .
			$templateIdentityForLog, GeneralUtility::SYSLOG_SEVERITY_INFO);
		return $stored;
	}

	/**
	 * @return ParsedTemplateInterface
	 */
	public function getParsedTemplate() {
		$templateIdentifier = $this->getTemplateIdentifier();
		$source = $this->getTemplateSource();
		if (FALSE === isset($this->templateCompiler) || 0 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler']) {
			$parsedTemplate = $this->templateParser->parse($source);
		} elseif (TRUE === $this->templateCompiler->has($templateIdentifier)) {
			$parsedTemplate = $this->templateCompiler->get($templateIdentifier);
		} else {
			$parsedTemplate = $this->templateParser->parse($source);
			if (TRUE === $parsedTemplate->isCompilable()) {
				$this->templateCompiler->store($templateIdentifier, $parsedTemplate);
			}
		}
		return $parsedTemplate;
	}

	/**
	 * Renders a section from the specified template w/o requring a call to the
	 * main render() method - allows for cherry-picking sections to render.
	 * @param string $sectionName
	 * @param array $variables
	 * @param boolean $optional
	 * @return string
	 */
	public function renderStandaloneSection($sectionName, $variables, $optional = TRUE) {
		$content = NULL;
		$this->baseRenderingContext->setControllerContext($this->controllerContext);
		$this->startRendering(AbstractTemplateView::RENDERING_TEMPLATE, $this->getParsedTemplate(), $this->baseRenderingContext);
		$content = $this->renderSection($sectionName, $variables, $optional);
		$this->stopRendering();
		return $content;
	}

	/**
	 * @param string $templateSource
	 * @return void
	 */
	public function setTemplateSource($templateSource) {
		$this->templateSource = $templateSource;
	}

	/**
	 * @param string $actionName
	 * @return string
	 */
	protected function getTemplateSource($actionName = NULL) {
		if (NULL !== $this->templateSource) {
			return $this->templateSource;
		}
		return parent::getTemplateSource($actionName);
	}

	/**
	 * We use a checksum of the template source as the template identifier
	 *
	 * @param string $actionName
	 * @return string
	 */
	protected function getTemplateIdentifier($actionName = NULL) {
		$hasMethodOnParent = TRUE === method_exists(get_parent_class($this), __FUNCTION__);
		$templateFileExists = TRUE === file_exists($this->templatePathAndFilename);

		return TRUE === $hasMethodOnParent && TRUE === $templateFileExists ? parent::getTemplateIdentifier($actionName) : 'viewhelpertest_' . sha1($this->templateSource);
	}

	/**
	 * @param string $actionName
	 * @return string
	 */
	public function getTemplatePathAndFilename($actionName = NULL) {
		if (NULL !== $this->templatePathAndFilename) {
			return $this->templatePathAndFilename;
		}
		if (TRUE === empty($actionName)) {
			$actionName = $this->controllerContext->getRequest()->getControllerActionName();
		}
		$actionName = ResolveUtility::convertAllPathSegmentsToUpperCamelCase($actionName);
		$paths = $this->expandGenericPathPattern($this->templatePathAndFilenamePattern, FALSE, FALSE);
		foreach ($paths as &$templatePathAndFilename) {
			$templatePathAndFilename = str_replace('@action', $actionName, $templatePathAndFilename);
			if (TRUE === file_exists($templatePathAndFilename)) {
				return $templatePathAndFilename;
			}
		}
		return parent::getTemplatePathAndFilename($actionName);
	}
	/**
	 * Wrapper method to make the static call to GeneralUtility mockable in tests
	 *
	 * @param string $pathAndFilename
	 *
	 * @return string absolute pathAndFilename
	 */
	protected function resolveFileNamePath($pathAndFilename) {
		return '/' !== $pathAndFilename{0} ? GeneralUtility::getFileAbsFileName($pathAndFilename) : $pathAndFilename;
	}
}
