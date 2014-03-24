<?php
namespace FluidTYPO3\Flux\Hooks;
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

use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * WizardItems Hook Subscriber
 * @package Flux
 */
class WizardItemsHookSubscriber implements NewContentElementWizardHookInterface {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->injectObjectManager($objectManager);
		$configurationService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
		$this->injectConfigurationService($configurationService);
	}

	/**
	 * @param array $items
	 * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController
	 * @return void
	 */
	public function manipulateWizardItems(&$items, &$parentObject) {
		$whitelist = array();
		$blacklist = array();
		// if a Provider is registered for the "pages" table, try to get a Grid from it. If the Grid
		// returned contains a Column which matches the desired colPos value, attempt to read a list
		// of allowed/denied content element types from it.
		$pageRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'pages', "uid = '" . $parentObject->id . "'");
		$pageProviders = $this->configurationService->resolveConfigurationProviders('pages', NULL, $pageRecord);
		foreach ($pageProviders as $pageProvider) {
			$grid = $pageProvider->getGrid($pageRecord);
			if (NULL === $grid) {
				continue;
			}
			foreach ($grid->getRows() as $row) {
				foreach ($row->getColumns() as $column) {
					if ($column->getColumnPosition() === $parentObject->colPos) {
						list ($whitelist, $blacklist) = $this->appendToWhiteAndBlacklistFromComponent($column, $whitelist, $blacklist);
					}
				}
			}
		}
		// Detect what was clicked in order to create the new content element; decide restrictions
		// based on this.
		$defaultValues = GeneralUtility::_GET('defVals');
		if (0 > $parentObject->uid_pid) {
			// pasting after another element means we should try to resolve the Flux content relation
			// from that element instead of GET parameters (clicked: "create new" icon after other element)
			$relativeRecordUid = abs($parentObject->uid_pid);
			$relativeRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', "uid = '" . $relativeRecordUid . "'");
			$fluxAreaName = $relativeRecord['tx_flux_column'];
		} elseif (TRUE === isset($defaultValues['tt_content']['tx_flux_column'])) {
			// attempt to read the target Flux content area from GET parameters (clicked: "create new" icon
			// in top of nested Flux content area
			$fluxAreaName = $defaultValues['tt_content']['tx_flux_column'];
			$relativeRecordUid = $defaultValues['tt_content']['tx_flux_parent'];
		}
		// if these variables now indicate that we are inserting content elements into a Flux-enabled content
		// area inside another content element, attempt to read allowed/denied content types from the
		// Grid returned by the Provider that applies to the parent element's type and configuration
		// (admitted, that's quite a mouthful - but it's not that different from reading the values from
		// a page template like above; it's the same principle).
		if (0 < $relativeRecordUid && FALSE === empty($fluxAreaName)) {
			$relativeRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', "uid = '" . $relativeRecordUid . "'");
			$contentProviders = $this->configurationService->resolveConfigurationProviders('tt_content', NULL, $relativeRecord);
			foreach ($contentProviders as $contentProvider) {
				$grid = $contentProvider->getGrid($relativeRecord);
				if (NULL === $grid) {
					continue;
				}
				foreach ($grid->getRows() as $row) {
					foreach ($row->getColumns() as $column) {
						foreach ($column->getAreas() as $area) {
							if ($area->getName() === $fluxAreaName) {
								list ($whitelist, $blacklist) = $this->appendToWhiteAndBlacklistFromComponent($area, $whitelist, $blacklist);
							}
						}
					}
				}
			}
		}
		// White/blacklist filtering. If whitelist contains elements, filter the list
		// of possible types by whitelist first. Then apply the blacklist, removing
		// any element types recorded herein.
		$whitelist = array_unique($whitelist);
		$blacklist = array_unique($blacklist);
		if (0 < count($whitelist)) {
			foreach ($items as $name => $item) {
				if (FALSE !== strpos($name, '_') && FALSE === in_array($item['tt_content_defValues']['CType'], $whitelist)) {
					unset($items[$name]);
				}
			}
		}
		if (0 < count($blacklist)) {
			foreach ($blacklist as $contentElementType) {
				foreach ($items as $name => $item) {
					if ($item['tt_content_defValues']['CType'] === $contentElementType) {
						unset($items[$name]);
					}
				}
			}
		}
		// Finally, loop through the items list and clean up any tabs with zero element types inside.
		$preserveHeaders = array();
		foreach ($items as $name => $item) {
			if (FALSE !== strpos($name, '_')) {
				array_push($preserveHeaders, reset(explode('_', $name)));
			}
		}
		foreach ($items as $name => $item) {
			if (FALSE === strpos($name, '_') && FALSE === in_array($name, $preserveHeaders)) {
				unset($items[$name]);
			}
		}
	}

	protected function appendToWhiteAndBlacklistFromComponent(FormInterface $component, array $whitelist, array $blacklist) {
		$allowed = $component->getVariable('allowedContentTypes');
		if (NULL !== $allowed) {
			$whitelist = array_merge($whitelist, GeneralUtility::trimExplode(',', $allowed));
		}
		$denied = $component->getVariable('deniedContentTypes');
		if (NULL !== $denied) {
			$blacklist = array_merge($blacklist, GeneralUtility::trimExplode(',', $denied));
		}
		return array($whitelist, $blacklist);
	}

}
