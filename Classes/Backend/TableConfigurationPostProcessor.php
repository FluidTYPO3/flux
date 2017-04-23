<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Helper\ContentTypeBuilder;
use FluidTYPO3\Flux\Helper\Resolver;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\AnnotationUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Exception;

/**
 * Table Configuration (TCA) post processor
 *
 * Simply loads the Flux service and lets methods
 * on this Service load necessary configuration.
 */
class TableConfigurationPostProcessor implements TableConfigurationPostProcessingHookInterface
{

    /**
     * @var array
     */
    private static $tableTemplate = [
        'title' => null,
        'label' => null,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'enablecolumns' => [],
        'iconfile' => '',
        'hideTable' => false,
    ];

    /**
     * @return void
     */
    public function processData()
    {
        if (TYPO3_REQUESTTYPE_INSTALL !== (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
            $this->spoolQueuedContentTypeRegistrations(Core::getQueuedContentTypeRegistrations());
            $this->generateTableConfigurationForProviderForms();
        }
    }

    /**
     * @param array $queue
     * @return void
     */
    public static function spoolQueuedContentTypeTableConfigurations(array $queue)
    {
        $contentTypeBuilder = new ContentTypeBuilder();
        foreach ($queue as $queuedRegistration) {
            list ($providerExtensionName, $templatePathAndFilename) = $queuedRegistration;
            $contentType = static::determineContentType($providerExtensionName, $templatePathAndFilename);
            $contentTypeBuilder->addBoilerplateTableConfiguration($contentType);
        }
    }

    /**
     * @param string $providerExtensionName
     * @param string $templatePathAndFilename
     * @return string
     */
    protected static function determineContentType($providerExtensionName, $templatePathAndFilename)
    {
        // Determine which plugin name and controller action to emulate with this CType, base on file name.
        $controllerExtensionName = $providerExtensionName;
        if (!static::controllerExistsInExtension($providerExtensionName, 'Content')) {
            $controllerExtensionName = 'FluidTYPO3.Flux';
        }
        $emulatedPluginName = ucfirst(pathinfo($templatePathAndFilename, PATHINFO_FILENAME));
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($controllerExtensionName));
        $fullContentType = $extensionSignature . '_' . strtolower($emulatedPluginName);
        return $fullContentType;
    }

    /**
     * @param string $providerExtensionName
     * @param string $controllerName
     * @return boolean
     */
    protected static function controllerExistsInExtension($providerExtensionName, $controllerName)
    {
        $controllerClassName = str_replace('.', '\\', $providerExtensionName) . '\\Controller\\' . $controllerName . 'Controller';
        return class_exists($controllerClassName);
    }

    /**
     * @param array $queue
     * @return void
     */
    protected function spoolQueuedContentTypeRegistrations(array $queue)
    {
        $contentTypeBuilder = new ContentTypeBuilder();
        foreach ($queue as $queuedRegistration) {
            /** @var ProviderInterface $provider */
            list ($providerExtensionName, $templateFilename) = $queuedRegistration;
            try {
                $provider = $contentTypeBuilder->configureContentTypeFromTemplateFile(
                    $providerExtensionName,
                    $templateFilename
                );

                Core::registerConfigurationProvider($provider);

                $controllerExtensionName = $providerExtensionName;
                if (!static::controllerExistsInExtension($providerExtensionName, 'Content')) {
                    $controllerExtensionName = 'FluidTYPO3.Flux';
                }

                $contentType = static::determineContentType($providerExtensionName, $templateFilename);
                $pluginName = ucfirst(pathinfo($templateFilename, PATHINFO_FILENAME));
                $contentTypeBuilder->registerContentType($controllerExtensionName, $contentType, $provider, $pluginName);

            } catch (Exception $error) {
                if (!Bootstrap::getInstance()->getApplicationContext()->isProduction()) {
                    throw $error;
                }
                GeneralUtility::sysLog(
                    sprintf(
                        'Template %s count not be used as content type: %s',
                        $templateFilename,
                        $error->getMessage()
                    ),
                    'flux'
                );
            }
        }
    }

    /**
     * @return void
     */
    protected function generateTableConfigurationForProviderForms()
    {
        GeneralUtility::logDeprecatedFunction();
        $resolver = new Resolver();
        $forms = Core::getRegisteredFormsForTables();
        $packages = $this->getInstalledFluxPackages();
        $models = $resolver->resolveDomainFormClassInstancesFromPackages($packages);
        foreach ($forms as $fullTableName => $form) {
            $this->processFormForTable($fullTableName, $form);
        }
        foreach ($models as $modelClassName => $form) {
            $fullTableName = $resolver->resolveDatabaseTableName($modelClassName);
            if (null === $form) {
                $form = $this->generateFormInstanceFromClassName($modelClassName, $fullTableName);
            }
            if (null === $form->getName()) {
                $form->setName($fullTableName);
            }
            $this->processFormForTable($fullTableName, $form);
        }
    }

    /**
     * @return array
     */
    protected function getInstalledFluxPackages()
    {
        GeneralUtility::logDeprecatedFunction();
        return array_keys(Core::getRegisteredPackagesForAutoForms());
    }

    /**
     * @param string $table
     * @param Form $form
     */
    protected function processFormForTable($table, Form $form)
    {
        GeneralUtility::logDeprecatedFunction();
        $extensionName = $form->getExtensionName();
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
        $tableConfiguration = self::$tableTemplate;
        $fields = [];
        $labelFields = $form->getOption(Form::OPTION_TCA_LABELS);
        $enableColumns = [];
        foreach ($form->getFields() as $field) {
            $name = $field->getName();
            // note: extracts the TCEforms sub-array from the configuration, as required in TCA.
            $fields[$name] = array_pop($field->build());
        }
        if (true === $form->getOption(Form::OPTION_TCA_HIDE)) {
            $enableColumns['disabled'] = 'hidden';
        }
        if (true === $form->getOption(Form::OPTION_TCA_START)) {
            $enableColumns['start'] = 'starttime';
        }
        if (true === $form->getOption(Form::OPTION_TCA_END)) {
            $enableColumns['end'] = 'endtime';
        }
        if (true === $form->getOption(Form::OPTION_TCA_FEGROUP)) {
            $enableColumns['fe_group'] = 'fe_group';
        }
        $tableConfiguration['iconfile'] = ExtensionManagementUtility::extRelPath($extensionKey) .
            $form->getOption(Form::OPTION_ICON);
        $tableConfiguration['enablecolumns'] = $enableColumns;
        $tableConfiguration['title'] = $form->getLabel();
        $tableConfiguration['languageField'] = 'sys_language_uid';
        $showRecordsFieldList = $this->buildShowItemList($form);
        $GLOBALS['TCA'][$table] = [
            'ctrl' => $tableConfiguration,
            'interface' => [
                'showRecordFieldList' => implode(',', array_keys($fields))
            ],
            'columns' => $fields,
            'types' => [
                0 => [
                    'showitem' => $showRecordsFieldList
                ]
            ]
        ];
        if (true === $form->getOption(Form::OPTION_TCA_DELETE)) {
            $GLOBALS['TCA'][$table]['ctrl']['delete'] = 'deleted';
        }
        if (null === $labelFields) {
            reset($fields);
            $GLOBALS['TCA'][$table]['ctrl']['label'] = key($fields);
        } else {
            $GLOBALS['TCA'][$table]['ctrl']['label'] = array_shift($labelFields);
            $GLOBALS['TCA'][$table]['ctrl']['label_alt'] = implode(',', $labelFields);
        }
    }

    /**
     * @param string $class
     * @param string $table
     * @return FormInterface
     */
    public function generateFormInstanceFromClassName($class, $table)
    {
        GeneralUtility::logDeprecatedFunction();
        $labelFields = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Label', false);
        $iconAnnotation = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Icon');
        $extensionName = $this->getExtensionNameFromModelClassName($class);
        $values = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Field', false);
        $sheets = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Sheet', false);
        $labels = true === is_array($labelFields) ? array_keys($labelFields) : [key($values)];
        foreach ($labels as $index => $labelField) {
            $labels[$index] = GeneralUtility::camelCaseToLowerCaseUnderscored($labelField);
        }
        $icon = true === isset($iconAnnotation['config']['path']) ? $iconAnnotation['config']['path'] : 'ext_icon.png';
        $hasVisibilityToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass(
            $class,
            'Flux\Control\Hide'
        );
        $hasDeleteToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass(
            $class,
            'Flux\Control\Delete'
        );
        $hasStartTimeToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass(
            $class,
            'Flux\Control\StartTime'
        );
        $hasEndTimeToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass(
            $class,
            'Flux\Control\EndTime'
        );
        $hasFrontendGroupToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass(
            $class,
            'Flux\Control\FrontendUserGroup'
        );
        $form = Form::create();
        $form->setName($table);
        $form->setExtensionName($extensionName);
        $form->setOption('labels', $labels);
        $form->setOption('delete', $hasDeleteToggle);
        $form->setOption('hide', $hasVisibilityToggle);
        $form->setOption('start', $hasStartTimeToggle);
        $form->setOption('end', $hasEndTimeToggle);
        $form->setOption(Form::OPTION_ICON, $icon);
        $form->setOption('frontendUserGroup', $hasFrontendGroupToggle);
        $fields = [];
        foreach ($sheets as $propertyName => $sheetAnnotation) {
            $sheetName = $sheetAnnotation['type'];
            if (false === isset($fields[$sheetName])) {
                $fields[$sheetName] = [];
            }
            array_push($fields[$sheetName], $propertyName);
        }
        foreach ($fields as $sheetName => $propertyNames) {
            $form->remove($sheetName);
            $sheet = $form->createContainer('Sheet', $sheetName);
            foreach ($propertyNames as $propertyName) {
                $settings = $values[$propertyName];
                $propertyName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
                if (true === isset($settings['type'])) {
                    $fieldType = implode('/', array_map('ucfirst', explode('.', $settings['type'])));
                    $field = $sheet->createField($fieldType, $propertyName);
                    foreach ($settings['config'] as $settingName => $settingValue) {
                        ObjectAccess::setProperty($field, $settingName, $settingValue);
                    }
                }
            }
        }
        return $form;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function getExtensionNameFromModelClassName($class)
    {
        GeneralUtility::logDeprecatedFunction();
        if (false !== strpos($class, '_')) {
            $parts = explode('_Domain_Model_', $class);
            $extensionName = substr($parts[0], 3);
        } else {
            $parts = explode('\\', $class);
            $candidate = array_slice($parts, 0, -3);
            if (1 === count($candidate)) {
                $extensionName = reset($candidate);
            } else {
                $extensionName = implode('.', $candidate);
            }
        }
        return $extensionName;
    }

    /**
     * @param Form $form
     * @return string
     */
    protected function buildShowItemList(Form $form)
    {
        GeneralUtility::logDeprecatedFunction();
        $parts = [];
        foreach ($form->getSheets(false) as $sheet) {
            array_push($parts, '--div--;' . $sheet->getLabel());
            foreach ($sheet->getFields() as $field) {
                array_push($parts, $field->getName());
            }
        }
        return implode(', ', $parts);
    }
}
