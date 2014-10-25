<?php
namespace FluidTYPO3\Flux;
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

use FluidTYPO3\Flux\Package\PackageInterface;
use FluidTYPO3\Flux\Package\PackageSetting;
use FluidTYPO3\Flux\Package\PluginDefinition;
use FluidTYPO3\Flux\Package\StandardPackage;
use FluidTYPO3\Flux\PackageSettings\CompactSetting;
use FluidTYPO3\Flux\PackageSettings\DebugModeSetting;
use FluidTYPO3\Flux\PackageSettings\DisableCompilerSetting;
use FluidTYPO3\Flux\PackageSettings\HandleErrorsSetting;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FluxPackage
 */
class FluxPackage extends StandardPackage implements PackageInterface {

	/**
	 * @return void
	 */
	public function initializePlugins() {
		$fluxApiPlugin = new PluginDefinition('API', 'Flux API', NULL, array('Flux' => 'renderChildContent'));
		$fluxApiPlugin->setInsertable(FALSE);
		$this->plugins->add($fluxApiPlugin);
	}

	/**
	 * @return void
	 */
	public function initializeProviders() {
		/** @var ProviderInterface $fluxContentProvider */
		$fluxContentProvider = $this->objectManager->get('FluidTYPO3\\Flux\\Provider\\ContentProvider');
		$this->providers->add($fluxContentProvider);
	}

	/**
	 * @return void
	 */
	public function initializeSettings() {
		$current = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'];
		$this->settings->add(new DebugModeSetting((boolean) $current['debugmode']));
		$this->settings->add(new DisableCompilerSetting((boolean) $current['disableCompiler']));
		$this->settings->add(new CompactSetting((boolean) $current['compact']));
		$this->settings->add(new HandleErrorsSetting((boolean) $current['handleErrors']));
	}

	/**
	 * @return array
	 */
	public function getOutletClassNames() {
		return array(
			'FluidTYPO3\\Flux\\Outlet\\StandardOutlet'
		);
	}

	/**
	 * @return array
	 */
	public function getPipeClassNames() {
		return array(
			'FluidTYPO3\\Flux\\Outlet\\Pipe\\ControllerPipe',
			'FluidTYPO3\\Flux\\Outlet\\Pipe\\EmailPipe',
			'FluidTYPO3\\Flux\\Outlet\\Pipe\\FlashMessagePipe',
			'FluidTYPO3\\Flux\\Outlet\\Pipe\\StandardPipe',
			'FluidTYPO3\\Flux\\Outlet\\Pipe\\TypeConverterPipe'
		);
	}

}
