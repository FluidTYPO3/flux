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
use FluidTYPO3\Flux\Utility\ErrorUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Converts raw flexform xml into an associative array
 */
class DataViewHelper extends AbstractViewHelper implements CompilableInterface
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var FluxService
     */
    protected static $configurationService;

    /**
     * @var WorkspacesAwareRecordService
     */
    protected static $recordService;

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        static::$configurationService = $configurationService;
    }

    /**
     * @param WorkspacesAwareRecordService $recordService
     * @return void
     */
    public function injectRecordService(WorkspacesAwareRecordService $recordService)
    {
        static::$recordService = $recordService;
    }

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('table', 'string', 'Name of table that contains record with Flux field', true);
        $this->registerArgument('field', 'string', 'Name of Flux field in table', true);
        $this->registerArgument('uid', 'integer', 'UID of record to load (used if "record" attribute not used)');
        $this->registerArgument('record', 'array', 'Record containing Flux field (used if "uid" attribute not used)');
        $this->registerArgument('as', 'string', 'Optional name of variable to assign in tag content rendering');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();
        $as = $arguments['as'];
        $record = $arguments['record'];
        $uid = $arguments['uid'];
        $field = $arguments['field'];
        $table = $arguments['table'];

        if (null === $record && null === $as) {
            $record = $renderChildrenClosure();
        }
        if (null === $uid && null !== $record && true === isset($record['uid'])) {
            $uid = $record['uid'];
        }
        if (true === isset($GLOBALS['TCA'][$table]) && true === isset($GLOBALS['TCA'][$table]['columns'][$field])) {
            if (null === $record) {
                $record = static::getRecordService()->getSingle($table, 'uid,' . $field, $uid);
            }
            if (null === $record) {
                ErrorUtility::throwViewHelperException(
                    sprintf(
                        'Either table "%s", field "%s" or record with uid %d do not exist and you did not manually ' .
                        'provide the "record" attribute.',
                        $table,
                        $field,
                        $uid
                    ),
                    1358679983
                );
            }
            $providers = static::getFluxService()->resolveConfigurationProviders($table, $field, $record);
            $dataArray = static::readDataArrayFromProvidersOrUsingDefaultMethod($providers, $record, $field);
        } else {
            ErrorUtility::throwViewHelperException(
                'Invalid table:field "' . $table . ':' . $field . '" - does not exist in TYPO3 TCA.',
                1387049117
            );
        }
        if (null !== $as) {
            if ($templateVariableContainer->exists($as)) {
                $backupVariable = $templateVariableContainer->get($as);
                $templateVariableContainer->remove($as);
            }
            $templateVariableContainer->add($as, $dataArray);
            $content = $renderChildrenClosure();
            $templateVariableContainer->remove($as);
            if (true === isset($backupVariable)) {
                $templateVariableContainer->add($as, $backupVariable);
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
    protected static function readDataArrayFromProvidersOrUsingDefaultMethod(array $providers, $record, $field)
    {
        if (0 === count($providers)) {
            $lang = static::getCurrentLanguageName();
            $pointer = static::getCurrentValuePointerName();
            $dataArray = static::$configurationService->convertFlexFormContentToArray(
                $record[$field],
                null,
                $lang,
                $pointer
            );
        } else {
            $dataArray = [];
            /** @var ProviderInterface $provider */
            foreach ($providers as $provider) {
                $data = (array) $provider->getFlexFormValues($record);
                $dataArray = RecursiveArrayUtility::merge($dataArray, $data);
            }
        }
        return $dataArray;
    }

    /**
     * @return FluxService
     */
    protected static function getFluxService()
    {
        if (!isset(static::$configurationService)) {
            static::$configurationService = GeneralUtility::makeInstance(ObjectManager::class)->get(FluxService::class);
        }
        return static::$configurationService;
    }

    /**
     * @return WorkspacesAwareRecordService
     */
    protected static function getRecordService()
    {
        if (!isset(static::$recordService)) {
            static::$recordService = GeneralUtility::makeInstance(ObjectManager::class)->get(WorkspacesAwareRecordService::class);
        }
        return static::$recordService;
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
    protected static function getCurrentLanguageName()
    {
        $language = $GLOBALS['TSFE']->lang;
        if (true === empty($language) || 'default' === $language) {
            $language = null;
        }
        return $language;
    }

    /**
     * Gets the pointer name to use whne retrieving values from a
     * flexform source. Return NULL when pointer is default.
     *
     * @return string|NULL
     */
    protected static function getCurrentValuePointerName()
    {
        return static::getCurrentLanguageName();
    }
}
