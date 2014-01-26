<?php
namespace FluidTYPO3\Flux\ViewHelpers\Permission;
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

use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Permission\PermissionInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
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
abstract class AbstractPermissionViewHelper extends AbstractFormViewHelper {

	/**
	 * @return void
	 */
	public function render() {
		$container = $this->getContainer();
		$permission = $this->getPermissionInstance();
		$container->requirePermission($permission);
	}

	/**
	 * @return ContainerInterface
	 */
	protected function getContainer() {
		if (TRUE === $this->viewHelperVariableContainer->exists('FluidTYPO3\Flux\ViewHelpers\Permission\AbstractPermissionViewHelper', 'permission')) {
			return $this->viewHelperVariableContainer->get('FluidTYPO3\Flux\ViewHelpers\Permission\AbstractPermissionViewHelper', 'permission');
		}
		return parent::getContainer();
	}


	/**
	 * @return PermissionInterface
	 */
	protected function getPermissionInstance() {
		$permissionClassName = $this->getPermissionClassName();
		/** @var PermissionInterface $permission */
		$permission = $this->objectManager->get($permissionClassName);
		return $permission;
	}

	/**
	 * @return string
	 */
	protected function getPermissionClassName() {
		$viewHelperClassName = get_class($this);
		$permissionClassName = str_replace('\ViewHelpers\Permission\\', '\Permission\\', $viewHelperClassName);
		$permissionClassName = substr($permissionClassName, 0, -10);
		return $permissionClassName;
	}

}
