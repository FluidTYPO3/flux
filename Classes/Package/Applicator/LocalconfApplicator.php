<?php
namespace FluidTYPO3\Flux\Package\Applicator;
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

use FluidTYPO3\Flux\Collection\Collection;
use FluidTYPO3\Flux\Package\PackageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Service\PackageService;
use FluidTYPO3\Flux\Package\PluginDefinition;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Class LocalconfApplicator
 *
 * Applies runtime configuration normally contained in "ext_localconf.php"
 * which is why the class is named thusly.
 */
class LocalconfApplicator extends AbstractApplicator {

	/**
	 * @param PackageInterface $package
	 * @param PluginDefinition $plugin
	 * @return void
	 */
	protected function applyPluginDefinition(PackageInterface $package, PluginDefinition $plugin) {
		ExtensionUtility::configurePlugin(
			$package->getName(),
			$plugin->getName(),
			(array) $plugin->getControllerActions(),
			(array) $plugin->getUncachedControllerActions(),
			TRUE === $plugin->getAsContentType() ? ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT : ExtensionUtility::PLUGIN_TYPE_PLUGIN
		);
	}

}
