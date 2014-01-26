<?php
namespace FluidTYPO3\Flux\ViewHelpers\Permission\Compound;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
 *****************************************************************/

use FluidTYPO3\Flux\ViewHelpers\Permission\AbstractPermissionViewHelper;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Base Permission-instance ViewHelper
 *
 * Creates Permissions which can be attached to FormInterface
 * implementers - e.g. parent nodes of the PermissionViewHelper
 * node. Permission is then assigned to the parent.
 *
 * @package Flux
 * @subpackage ViewHelpers/Permission
 */
abstract class AbstractCompoundPermissionViewHelper extends AbstractPermissionViewHelper {

	/**
	 * @return void
	 */
	public function render() {
		$permission = $this->getPermissionInstance();
		$container = $this->getContainer();
		$container->requirePermission($permission);
		$this->viewHelperVariableContainer->addOrUpdate('FluidTYPO3\Flux\ViewHelpers\Permission\AbstractPermissionViewHelper', 'permission', $permission);
		$this->renderChildren();
		$this->viewHelperVariableContainer->remove('FluidTYPO3\Flux\ViewHelpers\Permission\AbstractPermissionViewHelper', 'permission');
	}

}
