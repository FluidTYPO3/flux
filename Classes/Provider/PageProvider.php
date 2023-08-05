<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Page Configuration Provider
 *
 * Main Provider - triggers only on
 * records which have a selected action.
 * All other page records will be associated
 * with the SubPageProvider instead.
 */
class PageProvider extends AbstractProvider implements ProviderInterface
{
    const FIELD_NAME_MAIN = 'tx_fed_page_flexform';
    const FIELD_NAME_SUB = 'tx_fed_page_flexform_sub';
    const FIELD_ACTION_MAIN = 'tx_fed_page_controller_action';
    const FIELD_ACTION_SUB = 'tx_fed_page_controller_action_sub';

    protected ?string $tableName = 'pages';
    protected ?string $parentFieldName = 'pid';
    protected ?string $fieldName = self::FIELD_NAME_MAIN;
    protected string $extensionKey = 'FluidTYPO3.Flux';
    protected ?string $controllerName = 'Page';
    protected ?string $configurationSectionName = 'Configuration';
    protected ?string $pluginName = 'Page';

    private static array $cache = [];

    protected PageService $pageService;

    public function __construct(
        FluxService $configurationService,
        WorkspacesAwareRecordService $recordService,
        ViewBuilder $viewBuilder,
        PageService $pageService
    ) {
        parent::__construct($configurationService, $recordService, $viewBuilder);
        $this->pageService = $pageService;
    }

    /**
     * Returns TRUE that this Provider should trigger if:
     *
     * - table matches 'pages'
     * - field is NULL or matches self::FIELD_NAME
     * - a selection was made in the "template for this page" field
     */
    public function trigger(array $row, ?string $table, ?string $field, ?string $extensionKey = null): bool
    {
        $isRightTable = ($table === $this->tableName);
        $isRightField = in_array($field, [null, self::FIELD_NAME_MAIN, self::FIELD_NAME_SUB], true);
        return (true === $isRightTable && true === $isRightField);
    }

    public function getForm(array $row, ?string $forField = null): ?Form
    {
        if ($row['deleted'] ?? false) {
            return null;
        }

        $pageTemplateConfiguration = $this->pageService->getPageTemplateConfiguration($row['uid']);

        // If field is main field and "this page" has no selection or field is sub field and "subpages" has no selection
        if (($forField === self::FIELD_NAME_MAIN && empty($row[self::FIELD_ACTION_MAIN]))
            || ($forField === self::FIELD_NAME_SUB && empty($row[self::FIELD_ACTION_SUB]))
        ) {
            // The page inherits page layout from parent(s). Read the root line for the first page that defines a value
            // in the sub-action field, then use that record and resolve the Form used in the sub-configuration field.
            $form = parent::getForm($pageTemplateConfiguration['record_sub'] ?? $row, self::FIELD_NAME_SUB);
        } else {
            $form = parent::getForm($row, $forField);
        }

        if ($form) {
            $form->setOption(PreviewView::OPTION_PREVIEW, [PreviewView::OPTION_MODE => 'none']);
            $form = $this->setDefaultValuesInFieldsWithInheritedValues($form, $row);
        }

        return $form;
    }

    public function getExtensionKey(array $row, ?string $forField = null): string
    {
        $controllerExtensionKey = $this->getControllerExtensionKeyFromRecord($row, $forField);
        if (!empty($controllerExtensionKey)) {
            return ExtensionNamingUtility::getExtensionKey($controllerExtensionKey);
        }
        return $this->extensionKey;
    }

    public function getTemplatePathAndFilename(array $row, ?string $forField = null): ?string
    {
        $templatePathAndFilename = $this->templatePathAndFilename;
        $action = $this->getControllerActionFromRecord($row, $forField);
        if (!empty($action)) {
            $pathsOrExtensionKey = $this->templatePaths
                ?? ExtensionNamingUtility::getExtensionKey($this->getControllerExtensionKeyFromRecord($row, $forField));
            $templatePaths = $this->createTemplatePaths($pathsOrExtensionKey);
            $action = ucfirst($action);
            $templatePathAndFilename = $templatePaths->resolveTemplateFileForControllerAndActionAndFormat(
                $this->getControllerNameFromRecord($row),
                $action
            );
        }
        return $templatePathAndFilename;
    }

    public function getControllerExtensionKeyFromRecord(array $row, ?string $forField = null): string
    {
        $action = $this->getControllerActionReferenceFromRecord($row, $forField);
        $offset = strpos($action, '->');
        if ($offset !== false) {
            return substr($action, 0, $offset);
        }
        return $this->extensionKey;
    }

    public function getControllerActionFromRecord(array $row, ?string $forField = null): string
    {
        $action = $this->getControllerActionReferenceFromRecord($row, $forField);
        $parts = explode('->', $action);
        $controllerActionName = end($parts);
        if (empty($controllerActionName)) {
            return 'default';
        }
        $controllerActionName[0] = strtolower($controllerActionName[0]);
        return $controllerActionName;
    }

    public function getControllerActionReferenceFromRecord(array $row, ?string $forField = null): string
    {
        if (($forField === self::FIELD_NAME_MAIN || $forField === null) && !empty($row[self::FIELD_ACTION_MAIN])) {
            return is_array($row[self::FIELD_ACTION_MAIN])
                ? $row[self::FIELD_ACTION_MAIN][0]
                : $row[self::FIELD_ACTION_MAIN];
        } elseif ($forField === self::FIELD_NAME_SUB && !empty($row[self::FIELD_ACTION_SUB])) {
            return is_array($row[self::FIELD_ACTION_SUB])
                ? $row[self::FIELD_ACTION_SUB][0]
                : $row[self::FIELD_ACTION_SUB];
        }
        if (isset($row['uid'])) {
            $configuration = $this->pageService->getPageTemplateConfiguration((integer) $row['uid']);
            $fieldName = self::FIELD_ACTION_SUB;
            if ($forField === self::FIELD_NAME_MAIN) {
                $fieldName = self::FIELD_ACTION_MAIN;
            }
            return ($configuration[$fieldName] ?? 'flux->default') ?: 'flux->default';
        }
        return 'flux->default';
    }

    public function getFlexFormValues(array $row, ?string $forField = null): array
    {
        $immediateConfiguration = $this->getFlexFormValuesSingle($row);
        $inheritedConfiguration = $this->getInheritedConfiguration($row);
        return RecursiveArrayUtility::merge($inheritedConfiguration, $immediateConfiguration);
    }

    public function getFlexFormValuesSingle(array $row, ?string $forField = null): array
    {
        $fieldName = $forField ?? $this->getFieldName($row);
        $form = $this->getForm($row, $forField);
        $immediateConfiguration = $this->configurationService->convertFlexFormContentToArray(
            $row[$fieldName] ?? '',
            $form,
            null,
            null
        );
        return $immediateConfiguration;
    }

    public function postProcessRecord(
        string $operation,
        int $id,
        array $row,
        DataHandler $reference,
        array $removals = []
    ): bool {
        if ($operation === 'update') {
            $stored = $this->recordService->getSingle((string) $this->getTableName($row), '*', $id);
            if (!$stored) {
                return false;
            }
            $before = $stored;
            $tableName = $this->getTableName($stored);

            $record = RecursiveArrayUtility::mergeRecursiveOverrule(
                $stored,
                $reference->datamap[$this->tableName][$id] ?? []
            );

            foreach ([self::FIELD_NAME_MAIN, self::FIELD_NAME_SUB] as $tableFieldName) {
                $form = $this->getForm($record, $tableFieldName);
                if (!$form) {
                    continue;
                }

                $removeForField = [];
                foreach ($form->getFields() as $field) {
                    /** @var Form\Container\Sheet $parent */
                    $parent = $field->getParent();
                    $fieldName = (string) $field->getName();
                    $sheetName = (string) $parent->getName();
                    $inherit = (boolean) $field->getInherit();
                    $inheritEmpty = (boolean) $field->getInheritEmpty();
                    if (is_array($record[$tableFieldName]['data'] ?? null)) {
                        $value = $record[$tableFieldName]['data'][$sheetName]['lDEF'][$fieldName]['vDEF'] ?? null;
                        $inheritedConfiguration = $this->getInheritedConfiguration($record);
                        $inheritedValue = $this->getInheritedPropertyValueByDottedPath(
                            $inheritedConfiguration,
                            $fieldName
                        );
                        $empty = (true === empty($value) && $value !== '0' && $value !== 0);
                        $same = ($inheritedValue == $value);
                        if (true === $same && true === $inherit || (true === $inheritEmpty && true === $empty)) {
                            $removeForField[] = $fieldName;
                        }
                    }
                }
                $removeForField = array_merge(
                    $removeForField,
                    $this->extractFieldNamesToClear($record, $tableFieldName)
                );
                $removeForField = array_unique($removeForField);

                if (!empty($stored[$tableFieldName])) {
                    $stored[$tableFieldName] = MiscellaneousUtility::cleanFlexFormXml(
                        $stored[$tableFieldName],
                        $removeForField
                    );
                }
            }

            if ($before !== $stored) {
                $this->recordService->update((string) $tableName, $stored);
            }
        }

        return false;
    }

    /**
     * Gets an inheritance tree (ordered first parent -> ... -> root record)
     * of record arrays containing raw values, stopping at the first parent
     * record that defines a page layout to use in "page layout - subpages".
     */
    protected function getInheritanceTree(array $row): array
    {
        $records = $this->loadRecordTreeFromDatabase($row);
        foreach ($records as $index => $record) {
            $hasSubAction = false === empty($record[self::FIELD_ACTION_SUB]);
            if ($hasSubAction) {
                return array_slice($records, 0, $index + 1);
            }
        }
        return $records;
    }

    protected function setDefaultValuesInFieldsWithInheritedValues(Form $form, array $row): Form
    {
        $inheritedConfiguration = $this->getInheritedConfiguration($row);
        foreach ($form->getFields() as $field) {
            $name = (string) $field->getName();
            $inheritedValue = $this->getInheritedPropertyValueByDottedPath($inheritedConfiguration, $name);
            if (null !== $inheritedValue && true === $field instanceof Form\FieldInterface) {
                $field->setDefault($inheritedValue);
            }
        }
        return $form;
    }

    protected function getInheritedConfiguration(array $row): array
    {
        $tableName = $this->getTableName($row);
        $tableFieldName = $this->getFieldName($row);
        $uid = $row['uid'] ?? '';
        $cacheKey = $tableName . $tableFieldName . $uid;
        if (false === isset(self::$cache[$cacheKey])) {
            $tree = $this->getInheritanceTree($row);
            $data = [];
            foreach ($tree as $branch) {
                $values = $this->getFlexFormValuesSingle($branch, self::FIELD_NAME_SUB);
                $data = RecursiveArrayUtility::merge($data, $values);
            }
            self::$cache[$cacheKey] = $data;
        }
        return self::$cache[$cacheKey];
    }

    /**
     * @return mixed
     */
    protected function getInheritedPropertyValueByDottedPath(array $inheritedConfiguration, string $propertyPath)
    {
        if (true === empty($propertyPath)) {
            return null;
        } elseif (false === strpos($propertyPath, '.')) {
            if (isset($inheritedConfiguration[$propertyPath])) {
                return ObjectAccess::getProperty($inheritedConfiguration, $propertyPath);
            }
            return null;
        }
        return ObjectAccess::getPropertyPath($inheritedConfiguration, $propertyPath);
    }

    protected function unsetInheritedValues(Form\FormInterface $field, array $values): array
    {
        $name = $field->getName();
        $inherit = $field->getInherit();
        $inheritEmpty = $field->getInheritEmpty();
        $value = $values[$name] ?? null;
        $empty = empty($value) && !in_array($value, [0, '0'], true);
        if (!$inherit || ($inheritEmpty && $empty)) {
            unset($values[$name]);
        }
        return $values;
    }

    /**
     * @return mixed
     */
    protected function getParentFieldValue(array $row)
    {
        $parentFieldName = $this->getParentFieldName($row);
        if (null !== $parentFieldName && false === isset($row[$parentFieldName])) {
            $row = $this->recordService->getSingle((string) $this->getTableName($row), $parentFieldName, $row['uid']);
        }
        return $row[$parentFieldName] ?? null;
    }

    protected function loadRecordTreeFromDatabase(array $record): array
    {
        if (empty($record)) {
            return [];
        }
        /** @var RootlineUtility $rootLineUtility */
        $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, (integer) ($record['uid'] ?? 0));
        return array_slice($rootLineUtility->get(), 1);
    }
}
