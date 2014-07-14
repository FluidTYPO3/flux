<?php
namespace FluidTYPO3\Flux\Configuration;
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
 ***************************************************************/

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager as CoreConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Flux ConfigurationManager implementation
 *
 * More context-sensitive ConfigurationManager with TS resolve
 * methods optimised for use in the backend.
 *
 * @package Flux
 * @subpackage Configuraion
 */
class ConfigurationManager extends CoreConfigurationManager implements ConfigurationManagerInterface, SingletonInterface {

	/**
	 * @return void
	 */
	protected function initializeConcreteConfigurationManager() {
		if (TRUE === $this->environmentService->isEnvironmentInFrontendMode()) {
			$this->concreteConfigurationManager = $this->objectManager->get('TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager');
		} else {
			$this->concreteConfigurationManager = $this->objectManager->get('FluidTYPO3\Flux\Configuration\BackendConfigurationManager');
		}
	}

	/**
	 * @param integer $currentPageId
	 * @return void
	 */
	public function setCurrentPageUid($currentPageId) {
		if (TRUE === $this->concreteConfigurationManager instanceof BackendConfigurationManager) {
			$this->concreteConfigurationManager->setCurrentPageId($currentPageId);
		}
	}

	/**
	 * Extended page UID fetch
	 *
	 * Uses a range of additional page UID resolve methods to
	 * find the currently active page UID from URL, active
	 * record, etc.
	 *
	 * @return integer
	 */
	public function getCurrentPageId() {
		if (TRUE === $this->concreteConfigurationManager instanceof BackendConfigurationManager) {
			return $this->concreteConfigurationManager->getCurrentPageId();
		}
	}

}
