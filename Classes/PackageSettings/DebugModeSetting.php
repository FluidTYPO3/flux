<?php
namespace FluidTYPO3\Flux\PackageSettings;
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

use FluidTYPO3\Flux\Package\PackageSetting;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class DebugModeSetting
 */
class DebugModeSetting extends PackageSetting {

	/**
	 * Constructor
	 *
	 * @param mixed $value
	 */
	public function __construct($value) {
		parent::__construct('debugMode', 'boolean', self::DEFAULT_GROUP, '0', $value);
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return LocalizationUtility::translate('extensionmanager.' . $this->getName(), 'flux');
	}

}
