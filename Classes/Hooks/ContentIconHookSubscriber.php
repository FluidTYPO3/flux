<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

/**
 * Class ContentIconHookSubscriber
 */
class ContentIconHookSubscriber {

	/**
	 * @var array
	 */
	protected $templates = array(
		'iconWrapper' => '</div><span class="t3-icon t3-icon-empty t3-icon-empty-empty fluidcontent-icon">%s</span><div class="fluidcontent-hack">'
	);

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var FluxService
	 */
	protected $fluxService;

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
	 * @param FluxService $fluxService
	 * @return void
	 */
	public function injectFluxService(FluxService $fluxService) {
		$this->fluxService = $fluxService;
	}

	/**
	 * Construct
	 */
	public function __construct() {
		$this->injectObjectManager(GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager'));
		$this->injectFluxService($this->objectManager->get('FluidTYPO3\\Flux\\Service\\FluxService'));
		$this->cache = $this->objectManager->get('TYPO3\\CMS\\Core\\Cache\\CacheManager', $this->objectManager)->getCache('flux');
	}

	/**
	 * @param array $parameters
	 * @param PageLayoutView|DatabaseRecordList $caller
	 * @return string
	 */
	public function addSubIcon(array $parameters, $caller = NULL) {
		$this->attachAssets();
		list ($table, $uid, $record) = $parameters;
		$icon = NULL;
		if (NULL !== $caller) {
			$record = NULL === $record && 0 < $uid ? BackendUtility::getRecord($table, $uid) : $record;
			$cacheIdentity = $table . $uid . sha1(serialize($record));
			// filter 1: icon must not already be cached and both record and caller must be provided.
			if (TRUE === $this->cache->has($cacheIdentity)) {
				$icon = $this->cache->get($cacheIdentity);
			} elseif (NULL !== $record) {
				$field = $this->detectFirstFlexTypeFieldInTableFromPossibilities($table, array_keys($record));
				// filter 2: table must have one field defined as "flex" and record must include it.
				if (NULL !== $field && TRUE === isset($record[$field])) {
					// we check the cache here because at this point, the cache key is decidedly
					// unique and we have not yet consulted the (potentially costly) Provider.
					$provider = $this->fluxService->resolvePrimaryConfigurationProvider($table, $field, $record);
					// filter 3: a Provider must be resolved for the record.
					if (NULL !== $provider) {
						$form = $provider->getForm((array) $record);
						if (NULL !== $form) {
							$icon = MiscellaneousUtility::getIconForTemplate($form);
							if (NULL !== $icon) {
								$label = trim($form->getLabel());
								$icon = '<img width="16" height="16" src="' . $icon . '" alt="' . $label . '"
									title="' . $label . '" class="" />';
								$icon = sprintf($this->templates['iconWrapper'], $icon);
							}
						}
					}
				}
				$this->cache->set($cacheIdentity, $icon);
			}
		}
		return $icon;
	}

	/**
	 * @param string $table
	 * @param array $fields
	 * @return string
	 */
	protected function detectFirstFlexTypeFieldInTableFromPossibilities($table, $fields) {
		foreach ($fields as $fieldName) {
			if ('flex' === $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type']) {
				return $fieldName;
			}
		}
		return NULL;
	}

	/**
	 * @return void
	 */
	protected function attachAssets() {
		$GLOBALS['TBE_STYLES']['stylesheet'] = $doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/css/icon.css';
	}

}
