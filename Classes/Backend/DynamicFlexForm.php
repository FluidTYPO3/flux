<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Dynamic FlexForm insertion hook class
 */
class DynamicFlexForm {

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
	 * @param FluxService $service
	 * @return void
	 */
	public function injectConfigurationService(FluxService $service) {
		$this->configurationService = $service;
	}

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->injectObjectManager(GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager'));
		$this->injectConfigurationService($this->objectManager->get('FluidTYPO3\Flux\Service\FluxService'));
	}

	/**
	 * Hook for generating dynamic FlexForm source code
	 *
	 * @param array $dataStructArray
	 * @param array $conf
	 * @param array $row
	 * @param string $table
	 * @param string $fieldName
	 * @return void
	 */
	public function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, &$row, $table, $fieldName) {
		if (empty($fieldName) === TRUE) {
			// Cast NULL if an empty but not-NULL field name was passed. This has significance to the Flux internals in
			// respect to which ConfigurationProvider(s) are returned.
			$fieldName = NULL;
		}
		if (FALSE === is_array($dataStructArray)) {
			$dataStructArray = array();
		}
		$providers = $this->configurationService->resolveConfigurationProviders($table, $fieldName, $row);
		foreach ($providers as $provider) {
			$provider->postProcessDataStructure($row, $dataStructArray, $conf);
		}
		if (empty($dataStructArray)) {
			$dataStructArray = array('ROOT' => array('el' => array()));
		}
		// Trigger TCEforms dimension patching only if required by TYPO3 version according to CompatibilityRegistry.
		if (CompatibilityRegistry::get('FluidTYPO3\\Flux\\Backend\\DynamicFlexForm::NEEDS_TCEFORMS_WRAPPER')) {
			$dataStructArray = $this->patchTceformsWrapper($dataStructArray);
		}
	}

	/**
	 * Temporary method during FormEngine transition!
	 *
	 * Performs a duplication in data source, applying a wrapper
	 * around field configurations which require it for correct
	 * rendering in flex form containers.
	 *
	 * @param array $dataStructure
	 * @return array
	 */
	protected function patchTceformsWrapper(array $dataStructure) {
		foreach ($dataStructure as $index => $subStructure) {
			if (is_array($subStructure)) {
				$dataStructure[$index] = $this->patchTceformsWrapper($subStructure);
			}
		}
		if (isset($dataStructure['config']) && !isset($dataStructure['TCEforms'])) {
			$dataStructure = array('TCEforms' => $dataStructure);
		}
		return $dataStructure;
	}

}
