<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;

/**
 * Converts raw flexform xml into an associative array
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class DataViewHelper extends AbstractViewHelper {

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var WorkspacesAwareRecordService
	 */
	protected $recordService;

	/**
	 * Inject Flux service
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param WorkspacesAwareRecordService $recordService
	 * @return void
	 */
	public function injectRecordService(WorkspacesAwareRecordService $recordService) {
		$this->recordService = $recordService;
	}

	/**
	 * Render method
	 * @param string $table
	 * @param string $field
	 * @param integer $uid
	 * @param array $record
	 * @param string $as
	 * @return array
	 * @throws Exception
	 */
	public function render($table, $field, $uid = NULL, $record = NULL, $as = NULL) {
		if (NULL === $record && NULL === $as) {
			$record = $this->renderChildren();
		}
		if (NULL === $uid && NULL !== $record && TRUE === isset($record['uid'])) {
			$uid = $record['uid'];
		}
		if (TRUE === isset($GLOBALS['TCA'][$table]) && TRUE === isset($GLOBALS['TCA'][$table]['columns'][$field])) {
			if (NULL === $record) {
				$record = $this->recordService->getSingle($table, 'uid,' . $field, $uid);
			}
			if (NULL === $record) {
				throw new Exception(sprintf('Either table "%s", field "%s" or record with uid %d do not exist and you did not manually ' .
					'provide the "record" attribute.', $table, $field, $uid), 1358679983);
			}
			$providers = $this->configurationService->resolveConfigurationProviders($table, $field, $record);
			$dataArray = $this->readDataArrayFromProvidersOrUsingDefaultMethod($providers, $record, $field);
		} else {
			throw new Exception('Invalid table:field "' . $table . ':' . $field . '" - does not exist in TYPO3 TCA.', 1387049117);
		}
		if (NULL !== $as) {
			if ($this->templateVariableContainer->exists($as)) {
				$backupVariable = $this->templateVariableContainer->get($as);
				$this->templateVariableContainer->remove($as);
			}
			$this->templateVariableContainer->add($as, $dataArray);
			$content = $this->renderChildren();
			$this->templateVariableContainer->remove($as);
			if (TRUE === isset($backupVariable)) {
				$this->templateVariableContainer->add($as, $backupVariable);
			}
			return $content;
		}
		return $dataArray;
	}

	/**
	 * @param array $providers
	 * @param array $record
	 * @param string $field
	 * @return array
	 */
	protected function readDataArrayFromProvidersOrUsingDefaultMethod(array $providers, $record, $field) {
		if (0 === count($providers)) {
			$lang = $this->getCurrentLanguageName();
			$pointer = $this->getCurrentValuePointerName();
			$dataArray = $this->configurationService->convertFlexFormContentToArray($record[$field], NULL, $lang, $pointer);
		} else {
			$dataArray = array();
			/** @var ProviderInterface $provider */
			foreach ($providers as $provider) {
				$data = (array) $provider->getFlexFormValues($record);
				$dataArray = RecursiveArrayUtility::merge($dataArray, $data);
			}
		}
		return $dataArray;
	}

	/**
	 * Gets the current language name as string, in a format that is
	 * compatible with language pointers in a flexform. Usually this
	 * implies values like "en", "de" etc.
	 *
	 * Return NULL when language is site default language.
	 *
	 * @return string|NULL
	 */
	protected function getCurrentLanguageName() {
		$language = $GLOBALS['TSFE']->lang;
		if (TRUE === empty($language) || 'default' === $language) {
			$language = NULL;
		}
		return $language;
	}

	/**
	 * Gets the pointer name to use whne retrieving values from a
	 * flexform source. Return NULL when pointer is default.
	 *
	 * @return string|NULL
	 */
	protected function getCurrentValuePointerName() {
		return $this->getCurrentLanguageName();
	}

}
