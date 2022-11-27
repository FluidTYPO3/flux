<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Converts raw flexform xml into an associative array, and applies any
 * transformation that may be configured for fields/objects.
 *
 * ### Example: Fetch page configuration inside content element
 *
 * Since the `page` variable is available in fluidcontent elements, we
 * can use it to access page configuration data:
 *
 *     <flux:form.data table="pages" field="tx_fed_page_flexform" record="{page}" />
 *
 * ### Example: Check if page is accessible before loading data
 *
 * Data of disabled and deleted pages cannot be loaded with flux:form.data
 * and lead to an TYPO3FluidFluidCoreViewHelperException.
 * To prevent this exception, check if the page is accessible by generating
 * a link to it:
 *
 *     <f:if condition="{f:uri.page(pageUid: myUid)}">
 *         <flux:form.data table="pages" field="tx_fed_page_flexform" uid="{myUid}" as="pageSettings">
 *             ...
 *         </flux:form.data>
 *     </f:if>
 */
class DataViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var FluxService|null
     */
    protected static $configurationService;

    /**
     * @var WorkspacesAwareRecordService|null
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
        $templateVariableContainer = $renderingContext->getVariableProvider();
        $as = $arguments['as'];
        $record = $arguments['record'];
        $uid = $arguments['uid'] ?? null;
        $field = $arguments['field'] ?? null;
        $table = $arguments['table'] ?? null;

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
            if (!$record) {
                throw new Exception(
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
            throw new Exception(
                'Invalid table:field "' . $table . ':' . $field . '" - does not exist in TYPO3 TCA.',
                1387049117
            );
        }
        $dataArray = HookHandler::trigger(
            HookHandler::FORM_DATA_FETCHED,
            [
                'providers' => $providers,
                'record' => $record,
                'table' => $table,
                'field' => $field,
                'data' => $dataArray,
                'as' => $as,
                'variableProvider' => $templateVariableContainer
            ]
        )['data'];
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
            $dataArray = static::getFluxService()->convertFlexFormContentToArray($record[$field]);
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
     * @codeCoverageIgnore
     */
    protected static function getFluxService()
    {
        if (!isset(static::$configurationService)) {
            /** @var FluxService $fluxService */
            $fluxService = static::getObjectManager()->get(FluxService::class);
            static::$configurationService = $fluxService;
        }
        return static::$configurationService;
    }

    /**
     * @return WorkspacesAwareRecordService
     * @codeCoverageIgnore
     */
    protected static function getRecordService()
    {
        if (!isset(static::$recordService)) {
            /** @var WorkspacesAwareRecordService $workspacesAwareRecordService */
            $workspacesAwareRecordService = static::getObjectManager()->get(WorkspacesAwareRecordService::class);
            static::$recordService = $workspacesAwareRecordService;
        }
        return static::$recordService;
    }

    /**
     * @codeCoverageIgnore
     */
    protected static function getObjectManager(): ObjectManagerInterface
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager;
    }
}
