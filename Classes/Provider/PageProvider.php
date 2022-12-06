<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
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

    private static array $cache = [];

    protected PageService $pageService;

    public function __construct()
    {
        parent::__construct();

        /** @var PageService $pageService */
        $pageService = GeneralUtility::makeInstance(PageService::class);
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
        $isRightField = (null === $field || $field === $this->fieldName);
        return (true === $isRightTable && true === $isRightField);
    }

    public function getForm(array $row): ?Form
    {
        if ($row['deleted'] ?? false) {
            return null;
        }
        $form = parent::getForm($row);
        if ($form) {
            $form->setOption(PreviewView::OPTION_PREVIEW, [PreviewView::OPTION_MODE => 'none']);
            $form = $this->setDefaultValuesInFieldsWithInheritedValues($form, $row);
        }
        return $form;
    }

    public function getExtensionKey(array $row): string
    {
        $controllerExtensionKey = $this->getControllerExtensionKeyFromRecord($row);
        if (false === empty($controllerExtensionKey)) {
            return ExtensionNamingUtility::getExtensionKey($controllerExtensionKey);
        }
        return $this->extensionKey;
    }

    public function getTemplatePathAndFilename(array $row): ?string
    {
        $templatePathAndFilename = $this->templatePathAndFilename;
        $action = $this->getControllerActionReferenceFromRecord($row);
        if (false === empty($action)) {
            $pathsOrExtensionKey = $this->templatePaths
                ?? ExtensionNamingUtility::getExtensionKey($this->getControllerExtensionKeyFromRecord($row));
            $templatePaths = $this->createTemplatePaths($pathsOrExtensionKey);
            $action = $this->getControllerActionFromRecord($row);
            $action = ucfirst($action);
            $templatePathAndFilename = $templatePaths->resolveTemplateFileForControllerAndActionAndFormat(
                $this->getControllerNameFromRecord($row),
                $action
            );
        }
        return $templatePathAndFilename;
    }

    public function getControllerExtensionKeyFromRecord(array $row): string
    {
        $action = $this->getControllerActionReferenceFromRecord($row);
        $offset = strpos($action, '->');
        if (false !== $offset) {
            return substr($action, 0, $offset);
        }
        return $this->extensionKey;
    }

    public function getControllerActionFromRecord(array $row): string
    {
        $action = $this->getControllerActionReferenceFromRecord($row);
        $parts = explode('->', $action);
        $controllerActionName = end($parts);
        if (empty($controllerActionName)) {
            return 'default';
        }
        $controllerActionName[0] = strtolower($controllerActionName[0]);
        return $controllerActionName;
    }

    public function getControllerActionReferenceFromRecord(array $row): string
    {
        if (!empty($row[self::FIELD_ACTION_MAIN])) {
            return is_array($row[self::FIELD_ACTION_MAIN])
                ? $row[self::FIELD_ACTION_MAIN][0]
                : $row[self::FIELD_ACTION_MAIN];
        }
        if (isset($row['uid'])) {
            return ($this->pageService->getPageTemplateConfiguration($row['uid'])[self::FIELD_ACTION_SUB]
                ?? 'flux->default') ?: 'flux->default';
        }
        return 'flux->default';
    }

    public function getFlexFormValues(array $row): array
    {
        $immediateConfiguration = $this->getFlexFormValuesSingle($row);
        $inheritedConfiguration = $this->getInheritedConfiguration($row);
        return RecursiveArrayUtility::merge($inheritedConfiguration, $immediateConfiguration);
    }

    public function getFlexFormValuesSingle(array $row): array
    {
        $fieldName = $this->getFieldName($row);
        $form = $this->getForm($row);
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
        array &$row,
        DataHandler $reference,
        array $removals = []
    ): void {
        if ('update' === $operation) {
            $record = $this->recordService->getSingle((string) $this->getTableName($row), '*', $id);
            if ($record === null) {
                return;
            }
            if (isset($reference->datamap[$this->tableName][$id])) {
                $record = RecursiveArrayUtility::mergeRecursiveOverrule(
                    $record,
                    $reference->datamap[$this->tableName][$id]
                );
            }
            $form = $this->getForm($record);
            if ($form) {
                $tableFieldName = $this->getFieldName($record);
                foreach ($form->getFields() as $field) {
                    /** @var Form\Container\Sheet $parent */
                    $parent = $field->getParent();
                    $fieldName = (string) $field->getName();
                    $sheetName = (string) $parent->getName();
                    $inherit = (boolean) $field->getInherit();
                    $inheritEmpty = (boolean) $field->getInheritEmpty();
                    if (isset($record[$tableFieldName]['data']) && is_array($record[$tableFieldName]['data'])) {
                        $value = $record[$tableFieldName]['data'][$sheetName]['lDEF'][$fieldName]['vDEF'];
                        $inheritedConfiguration = $this->getInheritedConfiguration($record);
                        $inheritedValue = $this->getInheritedPropertyValueByDottedPath(
                            $inheritedConfiguration,
                            $fieldName
                        );
                        $empty = (true === empty($value) && $value !== '0' && $value !== 0);
                        $same = ($inheritedValue == $value);
                        if (true === $same && true === $inherit || (true === $inheritEmpty && true === $empty)) {
                            $removals[] = $fieldName;
                        }
                    }
                }
            }
        }
        parent::postProcessRecord($operation, $id, $row, $reference, $removals);
    }

    /**
     * Gets an inheritance tree (ordered parent -> ... -> this record)
     * of record arrays containing raw values.
     */
    protected function getInheritanceTree(array $row): array
    {
        $records = $this->loadRecordTreeFromDatabase($row);
        if (0 === count($records)) {
            return $records;
        }
        $template = $records[0][self::FIELD_ACTION_SUB];
        foreach ($records as $index => $record) {
            $hasMainAction = false === empty($record[self::FIELD_ACTION_MAIN]);
            $hasSubAction = false === empty($record[self::FIELD_ACTION_SUB]);
            $shouldUseMainTemplate = $template !== ($record[self::FIELD_ACTION_SUB] ?? null);
            $shouldUseSubTemplate = $template !== ($record[self::FIELD_ACTION_MAIN] ?? null);
            if (($hasMainAction && $shouldUseSubTemplate) || ($hasSubAction && $shouldUseMainTemplate)) {
                return array_slice($records, $index);
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
                /** @var SubPageProvider|null $provider */
                $provider = $this->configurationService->resolvePrimaryConfigurationProvider(
                    $this->tableName,
                    self::FIELD_NAME_SUB,
                    $branch
                );
                if (null === $provider) {
                    continue;
                }
                $form = $provider->getForm($branch);
                if (null === $form) {
                    continue;
                }
                $fields = $form->getFields();
                $values = $provider->getFlexFormValuesSingle($branch);
                foreach ($fields as $field) {
                    $values = $this->unsetInheritedValues($field, $values);
                }
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
        $empty = (isset($values[$name]) && empty($values[$name]) && $values[$name] !== '0' && $values[$name] !== 0);
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
        $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $record['uid'] ?? null);
        return array_reverse(array_slice($rootLineUtility->get(), 1));
    }
}
