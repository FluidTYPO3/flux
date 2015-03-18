<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
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
		$mock = $this->getMock($this->createInstanceClassName(), array('getContainer', 'getGrid'));
		$mock->expects($this->once())->method('getContainer')->will($this->returnValue(NULL));
		$mock->expects($this->once())->method('getGrid')->will($this->returnValue($grid));
		ObjectAccess::setProperty($mock, 'viewHelperVariableContainer', $viewHelperContainer, TRUE);
		ObjectAccess::setProperty($mock, 'controllerContext', $controllerContext, TRUE);
		$mock->render();
	}

}
