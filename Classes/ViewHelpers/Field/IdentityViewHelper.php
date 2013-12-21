<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Form\Field\Identity;

/**
 * Identity field ViewHelper
 *
 * Can be used inside Objects and Containers to generate an ID.
 * Created component gets automatically filled with a unique value.
 * You don't have to use this component - if you don't, the ID still
 * gets generated - but using this component allows you to read the
 * ID in a form field (read-only, will be empty until saved once).
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class IdentityViewHelper extends AbstractFieldViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		// This ViewHelper has zero arguments - prevent parent's arguments from being registered.
	}

	/**
	 * @return Identity
	 */
	public function getComponent() {
		/** @var Identity $identity */
		$identity = $this->getForm()->createField('Identity', 'id');
		return $identity;
	}

}
