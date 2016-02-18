<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

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
	 * @var WorkspacesAwareRecordService
	 */
	protected $recordService;

	/**
	 * @var VariableFrontend
	 */
	protected $cache;

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
	 * @param WorkspacesAwareRecordService $recordService
	 * @return void
	 */
	public function injectRecordService(WorkspacesAwareRecordService $recordService) {
		$this->recordService = $recordService;
	}

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->injectObjectManager(GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager'));
		$this->injectConfigurationService($this->objectManager->get('FluidTYPO3\Flux\Service\FluxService'));
		$this->injectRecordService($this->objectManager->get('FluidTYPO3\Flux\Service\WorkspacesAwareRecordService'));
		$this->cache = $this->objectManager->get('TYPO3\\CMS\\Core\\Cache\\CacheManager', $this->objectManager)->getCache('flux');
	}

	/**
	 * Hook for generating dynamic FlexForm source code.
	 *
	 * NOTE: patches data structure resolving in a way that solves
	 * a regression in the TYPO3 core when dealing with IRRE AJAX
	 * requests (in which the database record is no longer fetched
	 * by the controller). This patches not only data structure
	 * resolving for Flux data structures but indeed any data
	 * structure built using hooks or involving user functions which
	 * require the entire record (but when using hooks, supports
	 * only extensions which are loaded AFTER or depend on Flux).
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
		if (!empty($fieldName) && !isset($row[$fieldName])) {
			// Patch required (possibly temporary). Due to changes in TYPO3 in the new FormEngine we must fetch the
			// database record at this point when the record is incomplete, which happens when attempting to render
			// IRRE records. The reason is that the controller that creates the HTML does not fetch the record any
			// more - and that the AJAX request contains only the UID. So, we fetch the record here to ensure it
			// contains the necessary fields. DOES NOT WORK FOR NEW RECORDS - SEE COMMENTS BELOW.
			$row = $this->recordService->getSingle($table, '*', $row['uid']);
		}
		$defaultDataSourceCacheIdentifier = $table . '_' . $fieldName . '_' . sha1(serialize($conf));
		if (!$row) {
			// In the case that the database record cannot be fetched we are dealing with a new or otherwise deleted
			// or unidentifiable record. This happens primarily when AJAX requests are made to render IRRE records
			// without the parent record having been saved first. To accommodate this case we have to be slightly
			// creative and store a "default" data source definition which is identified based on a checksum of the
			// configuration provided. Whenever we are then unable to fetch a record, we can at least attempt to
			// locate a default data source in previously cached content. NB: we enforce a VERY high cache lifetime
			// and continually refresh it every time it is possible to render a new DS that can serve as default.
			$dataStructArray = (array) $this->cache->get($defaultDataSourceCacheIdentifier);
		} else {
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
			$evaluationParameters = array();
			$this->cache->set(
				$defaultDataSourceCacheIdentifier,
				$this->recursivelyEvaluateClosures($dataStructArray, $evaluationParameters),
				array(),
				(time() + 31536000)
			);
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
	protected function patchTceformsWrapper(array $dataStructure, $parentIndex = NULL) {
		foreach ($dataStructure as $index => $subStructure) {
			if (is_array($subStructure)) {
				$dataStructure[$index] = $this->patchTceformsWrapper($subStructure, $index);
			}
		}
		if (isset($dataStructure['config']['type']) && $parentIndex !== 'TCEforms') {
			$dataStructure = array('TCEforms' => $dataStructure);
		}
		return $dataStructure;
	}

	/**
	 * Method used to ensure that all Closures in the data
	 * structure are evaluated. The returned array is then
	 * serialisation-safe. Closures can occur whenever Flux
	 * fields of certain types are used, for example the
	 * "custom" field type (which generates a Closure that
	 * evaluates the tag content in a deferred manner).
	 *
	 * @param array $dataStructureArray
	 * @param array $parameters
	 * @return array
	 */
	protected function recursivelyEvaluateClosures(array $dataStructureArray, array $parameters) {
		foreach ($dataStructureArray as $key => $value) {
			if ($value instanceof \Closure) {
				$dataStructureArray[$key] = $value($parameters);
			} elseif (is_array($value)) {
				$dataStructureArray[$key] = $this->recursivelyEvaluateClosures($value, $parameters);
			}
		}
		return $dataStructureArray;
	}

}
