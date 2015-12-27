<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * ContentViewHelperTest
 */
class ContentViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @test
	 */
	public function createsGridIfNotSet() {
		/** @var ViewHelperVariableContainer $viewHelperContainer */
		$viewHelperContainer = $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		/** @var Request $request */
		$request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
		/** @var ControllerContext $controllerContext */
		$controllerContext = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext');
		$controllerContext->setRequest($request);
		$column = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Column', array('setName', 'setLabel'));
		$column->expects($this->once())->method('setName');
		$column->expects($this->once())->method('setLabel');
		$row = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Row', array('createContainer'));
		$grid = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Grid', array('createContainer'));
		$grid->expects($this->once())->method('createContainer')->will($this->returnValue($row));
		$row->expects($this->once())->method('createContainer')->will($this->returnValue($column));
		$mock = $this->getMock($this->createInstanceClassName(), array('dummy'));
		$viewHelperContainer->addOrUpdate(
			AbstractFormViewHelper::SCOPE,
			AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS,
			array('grid' => $grid)
		);
		$renderingcontext = $this->getMock(
			'TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface',
			array(
				'getTemplateVariableContainer', 'getViewHelperVariableContainer', 'getControllerContext'
			)
		);
		$renderingcontext->expects($this->atLeastOnce())->method('getViewHelperVariableContainer')->willReturn($viewHelperContainer);
		$renderingcontext->expects($this->atLeastOnce())->method('getTemplateVariableContainer')->willReturn(
			$this->getMock('TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer')
		);
		$renderingcontext->expects($this->any())->method('getControllerContext')->willReturn($controllerContext);
		$mock->setRenderingContext($renderingcontext);
		$mock->setArguments(array());
		$mock::getComponent($renderingcontext, array());
	}

}
