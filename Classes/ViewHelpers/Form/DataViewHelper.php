<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    protected static ?FormDataTransformer $formDataTransformer = null;
    protected static ?ProviderResolver $providerResolver = null;
    protected static ?WorkspacesAwareRecordService $recordService = null;

    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', 'Name of table that contains record with Flux field', true);
        $this->registerArgument('field', 'string', 'Name of Flux field in table', true);
        $this->registerArgument('uid', 'integer', 'UID of record to load (used if "record" attribute not used)');
        $this->registerArgument('record', 'array', 'Record containing Flux field (used if "uid" attribute not used)');
        $this->registerArgument('as', 'string', 'Optional name of variable to assign in tag content rendering');
    }

    /**
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $templateVariableContainer = $renderingContext->getVariableProvider();
        /** @var string|null $as */
        $as = $arguments['as'];
        /** @var array|null $record */
        $record = $arguments['record'] ?? null;
        /** @var int|null $uid */
        $uid = $arguments['uid'] ?? null;
        /** @var string $field */
        $field = $arguments['field'];
        /** @var string $table */
        $table = $arguments['table'];

        if (!$record && !$as) {
            $record = $renderChildrenClosure();
        }
        if (!$uid && is_array($record) && isset($record['uid'])) {
            $uid = $record['uid'];
        }
        if (isset($GLOBALS['TCA'][$table]) && isset($GLOBALS['TCA'][$table]['columns'][$field])) {
            if (!$record) {
                $record = static::getRecordService()->getSingle($table, 'uid,' . $field, (integer) $uid);
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
            $providers = static::getProviderResolver()->resolveConfigurationProviders($table, $field, $record);
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
        if ($as) {
            if ($templateVariableContainer->exists($as)) {
                $backupVariable = $templateVariableContainer->get($as);
                $templateVariableContainer->remove($as);
            }
            $templateVariableContainer->add($as, $dataArray);
            $content = $renderChildrenClosure();
            $templateVariableContainer->remove($as);
            if (isset($backupVariable)) {
                $templateVariableContainer->add($as, $backupVariable);
            }
            return $content;
        }
        return $dataArray;
    }

    protected static function readDataArrayFromProvidersOrUsingDefaultMethod(
        array $providers,
        array $record,
        string $field
    ): array {
        if (0 === count($providers)) {
            $dataArray = static::getFormDataTransformer()->convertFlexFormContentToArray($record[$field] ?? '');
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
     * @codeCoverageIgnore
     */
    protected static function getProviderResolver(): ProviderResolver
    {
        if (!isset(static::$providerResolver)) {
            /** @var ProviderResolver $providerResolver */
            $providerResolver = GeneralUtility::makeInstance(ProviderResolver::class);
            static::$providerResolver = $providerResolver;
        }
        return static::$providerResolver;
    }

    /**
     * @codeCoverageIgnore
     */
    protected static function getFormDataTransformer(): FormDataTransformer
    {
        if (!isset(static::$formDataTransformer)) {
            /** @var FormDataTransformer $formDataTransformer */
            $formDataTransformer = GeneralUtility::makeInstance(FormDataTransformer::class);
            static::$formDataTransformer = $formDataTransformer;
        }
        return static::$formDataTransformer;
    }

    /**
     * @codeCoverageIgnore
     */
    protected static function getRecordService(): WorkspacesAwareRecordService
    {
        if (!isset(static::$recordService)) {
            /** @var WorkspacesAwareRecordService $workspacesAwareRecordService */
            $workspacesAwareRecordService = GeneralUtility::makeInstance(WorkspacesAwareRecordService::class);
            static::$recordService = $workspacesAwareRecordService;
        }
        return static::$recordService;
    }
}
