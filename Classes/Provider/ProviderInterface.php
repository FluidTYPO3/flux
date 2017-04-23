<?php
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * ProviderInterface
 */
interface ProviderInterface
{
    /**
     * @param array $settings
     * @return void
     */
    public function loadSettings(array $settings);

    /**
     * Must return TRUE if this ConfigurationProvider instance wants
     * to be the one used for proccesing $row
     *
     * @param array $row
     * @param string $table
     * @param string $field
     * @param string $extensionKey
     * @return boolean
     */
    public function trigger(array $row, $table, $field, $extensionKey = null);

    /**
     * Returns an instance of \FluidTYPO3\Flux\View\ViewContext as required by this record,
     * with the provided RequestInterface instance as context.
     *
     * @param array $row
     * @param RequestInterface|NULL $request
     * @return ViewContext
     * @deprecated To be removed in next major release
     */
    public function getViewContext(array $row, RequestInterface $request = null);

    /**
     * Returns an instance of \FluidTYPO3\Flux\Form as required by this record.
     *
     * @param array $row
     * @return Form|NULL
     * @deprecated To be removed in next major release
     */
    public function getForm(array $row);

    /**
     * Returns a \FluidTYPO3\Flux\Form\Container\Grid as required by this record.
     *
     * @param array $row
     * @return Grid
     * @deprecated To be removed in next major release
     */
    public function getGrid(array $row);

    /**
     * Return the extension key this processor belongs to
     *
     * @param array $row The record which triggered the processing
     * @return string
     */
    public function getExtensionKey(array $row);

    /**
     * Get the absolute path to the template file containing the FlexForm
     * field and sheets configuration. EXT:myext... syntax allowed
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getTemplatePathAndFilename(array $row);

    /**
     * Get an array of variables that should be used when rendering the
     * FlexForm configuration
     *
     * @param array $row The record which triggered the processing
     * @return array|NULL
     */
    public function getTemplateVariables(array $row);

    /**
     * Get paths for rendering the template, usual format i.e. partialRootPath,
     * layoutRootPath, templateRootPath members must be in the returned array
     *
     * @param array $row
     * @return array
     * @deprecated To be removed in next major release
     */
    public function getTemplatePaths(array $row);

    /**
     * Get the section name containing the FlexForm configuration. Return NULL
     * if no sections are used. If you use sections in your template, you MUST
     * use a section to contain the FlexForm configuration
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getConfigurationSectionName(array $row);

    /**
     * @param string $listType
     */
    public function setListType($listType);

    /**
     * @return string
     */
    public function getListType();

    /**
     * @param string $contentObjectType
     */
    public function setContentObjectType($contentObjectType);

    /**
     * @return string
     */
    public function getContentObjectType();

    /**
     * Get the field name which will trigger processing
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getFieldName(array $row);

    /**
     * Get the list_type value that will trigger processing
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getTableName(array $row);

    /**
     * @param string $tableName
     * @return void
     */
    public function setTableName($tableName);

    /**
     * @param string $fieldName
     * @return void
     */
    public function setFieldName($fieldName);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @param string $extensionKey
     * @return ProviderInterface
     */
    public function setExtensionKey($extensionKey);

    /**
     * @param string $controllerName
     * @return ProviderInterface
     */
    public function setControllerName($controllerName);

    /**
     * @param string $controllerAction
     * @return ProviderInterface
     */
    public function setControllerAction($controllerAction);

    /**
     * @param array|NULL $templateVariables
     * @return ProviderInterface
     */
    public function setTemplateVariables($templateVariables);

    /**
     * @param string $templatePathAndFilename
     * @return ProviderInterface
     */
    public function setTemplatePathAndFilename($templatePathAndFilename);

    /**
     * @param array|NULL $templatePaths
     * @return ProviderInterface
     */
    public function setTemplatePaths($templatePaths);

    /**
     * @param string|NULL $configurationSectionName
     * @return ProviderInterface
     */
    public function setConfigurationSectionName($configurationSectionName);

    /**
     * Post-process the TCEforms DataStructure for a record associated
     * with this ConfigurationProvider
     *
     * @param array $row
     * @param mixed $dataStructure Array or string; should only be processed if argument is an array
     * @param array $conf
     * @return void
     */
    public function postProcessDataStructure(array &$row, &$dataStructure, array $conf);

    /**
     * Pre-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param array $row The record by reference. Changing fields' values changes the record's values before display
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param DataHandler $reference A reference to the DataHandler object that is currently displaying the record
     * @return void
     */
    public function preProcessRecord(array &$row, $id, DataHandler $reference);

    /**
     * @abstract
     * @param array $row The record data. Changing fields' values changes the record's values before display
     * @return integer
     */
    public function getPriority(array $row);

    /**
     * Returns [$header, $content) preview chunks
     *
     * @abstract
     * @param array $row The record data to be analysed for variables to use in a rendered preview
     * @return array
     */
    public function getPreview(array $row);

    /**
     * Post-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param array $row the record by reference. Changing fields' values changes the record's values before saving
     * @param DataHandler $reference A reference to the DataHandler object that is currently saving the record
     * @param array $removals Allows methods to pass an array of field names to remove from the stored Flux value
     * @return void
     */
    public function postProcessRecord($operation, $id, array &$row, DataHandler $reference, array $removals = []);

    /**
     * Post-process database operation for the table that this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param string $status TYPO3 operation identifier, i.e. "new" etc.
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param array $row The record by reference. Changing fields' values changes the record's values
     *                   just before saving after operation
     * @param DataHandler $reference A reference to the DataHandler object that is currently performing the operation
     * @return void
     */
    public function postProcessDatabaseOperation($status, $id, &$row, DataHandler $reference);

    /**
     * Pre-process a command executed on a record form the table this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param string $command
     * @param integer $id
     * @param array $row
     * @param integer $relativeTo
     * @param DataHandler $reference
     * @return void
     */
    public function preProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference);

    /**
     * Post-process a command executed on a record form the table this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param string $command
     * @param integer $id
     * @param array $row
     * @param integer $relativeTo
     * @param DataHandler $reference
     * @return void
     */
    public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference);

    /**
     * Processes the table configuration (TCA) for the table associated
     * with this Provider, as determined by the trigger() method. Gets
     * passed an instance of the record being edited/created along with
     * the current configuration array - and must return a complete copy
     * of the configuration array manipulated to the Provider's needs.
     *
     * @param array $row The record being edited/created
     * @param array $configuration
     * @return array The large FormEngine configuration array - see FormEngine documentation!
     */
    public function processTableConfiguration(array $row, array $configuration);

    /**
     * Perform operations upon clearing cache(s)
     *
     * @param array $command
     * @return void
     */
    public function clearCacheCommand($command = []);

    /**
     * Converts the contents of the provided row's Flux-enabled field,
     * at the same time running through the inheritance tree generated
     * by getInheritanceTree() in order to apply inherited values.
     *
     * @param array $row
     * @return array
     */
    public function getFlexFormValues(array $row);

    /**
     * Implement to return a controller action name associated with $row.
     * Default strategy: return base name of Provider class minus the "Provider" suffix.
     *
     * @param array $row
     * @return string
     */
    public function getControllerNameFromRecord(array $row);

    /**
     * @param array $row
     * @return string
     */
    public function getControllerExtensionKeyFromRecord(array $row);

    /**
     * Implement this and return a fully qualified VendorName.PackageName
     * value based on $row.
     *
     * @param array $row
     * @return string
     */
    public function getControllerPackageNameFromRecord(array $row);

    /**
     * @param array $row
     * @return string
     */
    public function getControllerActionFromRecord(array $row);

    /**
     * @param array $row
     * @return string
     */
    public function getControllerActionReferenceFromRecord(array $row);

    /**
     * @param Form $form
     */
    public function setForm(Form $form);

    /**
     * @param Grid $grid
     */
    public function setGrid(Grid $grid);
}
