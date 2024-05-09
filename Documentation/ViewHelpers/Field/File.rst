..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Field/FileViewHelper.php

:edit-on-github-link: Field/FileViewHelper.php
:navigation-title: field.file
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-file:

=========================================
Field.file ViewHelper `<flux:field.file>`
=========================================

Group (select supertype) FlexForm field ViewHelper, subtype "file"

Select and render an image
==========================

    <flux:field.file name="settings.image" allowed="jpg,png,svg" showThumbnails="1" />

Then use `<f:image>` to render the image in the frontend:

    <f:image src="{settings.image}"/>

`alt` and `title` tags are not loaded from the file's meta data record.
Use `<flux:field.inline.fal>` if you want this feature.

.. _fluidtypo3-flux-field-file_source:

Source code
===========

Go to the source code of this ViewHelper: `FileViewHelper.php (GitHub) <fluidtypo3/flux/development/Field/FileViewHelper.php>`__.

.. _fluidtypo3-flux-field-file_arguments:

Arguments
=========

The following arguments are available for `<flux:field.file>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-file-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-file-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-field-file-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-file-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-field-file-default_argument:

default
-------

..  confval:: default
    :name: fluidtypo3-flux-field-file-default
    :type: string
    :required: false

    Default value for this attribute

.. _fluidtypo3-flux-field-file-native_argument:

native
------

..  confval:: native
    :name: fluidtypo3-flux-field-file-native
    :type: boolean
    :required: false

    If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _fluidtypo3-flux-field-file-position_argument:

position
--------

..  confval:: position
    :name: fluidtypo3-flux-field-file-position
    :type: string
    :required: false

    Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _fluidtypo3-flux-field-file-required_argument:

required
--------

..  confval:: required
    :name: fluidtypo3-flux-field-file-required
    :type: boolean
    :required: false

    If TRUE, this attribute must be filled when editing the FCE

.. _fluidtypo3-flux-field-file-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-file-exclude
    :type: boolean
    :required: false

    If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _fluidtypo3-flux-field-file-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-file-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-file-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-field-file-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, disables the field in the FlexForm

.. _fluidtypo3-flux-field-file-requestupdate_argument:

requestUpdate
-------------

..  confval:: requestUpdate
    :name: fluidtypo3-flux-field-file-requestupdate
    :type: boolean
    :required: false

    If TRUE, the form is force-saved and reloaded when field value changes

.. _fluidtypo3-flux-field-file-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-file-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-file-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-file-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-file-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-file-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-file-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-file-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-file-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-field-file-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-field-file-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-file-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-field-file-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-file-config
    :type: mixed
    :Default: array ()
    :required: false

    Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _fluidtypo3-flux-field-file-validate_argument:

validate
--------

..  confval:: validate
    :name: fluidtypo3-flux-field-file-validate
    :type: string
    :Default: 'trim'
    :required: false

    FlexForm-type validation configuration for this input

.. _fluidtypo3-flux-field-file-size_argument:

size
----

..  confval:: size
    :name: fluidtypo3-flux-field-file-size
    :type: integer
    :Default: 1
    :required: false

    Size of the selector box

.. _fluidtypo3-flux-field-file-multiple_argument:

multiple
--------

..  confval:: multiple
    :name: fluidtypo3-flux-field-file-multiple
    :type: boolean
    :required: false

    If TRUE, allows selecting the same value multiple times

.. _fluidtypo3-flux-field-file-minitems_argument:

minItems
--------

..  confval:: minItems
    :name: fluidtypo3-flux-field-file-minitems
    :type: integer
    :required: false

    Minimum required number of items to be selected

.. _fluidtypo3-flux-field-file-maxitems_argument:

maxItems
--------

..  confval:: maxItems
    :name: fluidtypo3-flux-field-file-maxitems
    :type: integer
    :Default: 1
    :required: false

    Maxium allowed number of items to be selected

.. _fluidtypo3-flux-field-file-itemliststyle_argument:

itemListStyle
-------------

..  confval:: itemListStyle
    :name: fluidtypo3-flux-field-file-itemliststyle
    :type: string
    :required: false

    Overrides the default list style when maxItems > 1

.. _fluidtypo3-flux-field-file-selectedliststyle_argument:

selectedListStyle
-----------------

..  confval:: selectedListStyle
    :name: fluidtypo3-flux-field-file-selectedliststyle
    :type: string
    :required: false

    Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _fluidtypo3-flux-field-file-items_argument:

items
-----

..  confval:: items
    :name: fluidtypo3-flux-field-file-items
    :type: mixed
    :required: false

    Items for the selector; array / CSV / Traversable / Query supported

.. _fluidtypo3-flux-field-file-emptyoption_argument:

emptyOption
-----------

..  confval:: emptyOption
    :name: fluidtypo3-flux-field-file-emptyoption
    :type: mixed
    :required: false

    If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _fluidtypo3-flux-field-file-translatecsvitems_argument:

translateCsvItems
-----------------

..  confval:: translateCsvItems
    :name: fluidtypo3-flux-field-file-translatecsvitems
    :type: boolean
    :required: false

    If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _fluidtypo3-flux-field-file-itemsprocfunc_argument:

itemsProcFunc
-------------

..  confval:: itemsProcFunc
    :name: fluidtypo3-flux-field-file-itemsprocfunc
    :type: string
    :required: false

    Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _fluidtypo3-flux-field-file-maxsize_argument:

maxSize
-------

..  confval:: maxSize
    :name: fluidtypo3-flux-field-file-maxsize
    :type: integer
    :required: false

    Maximum file size allowed in KB

.. _fluidtypo3-flux-field-file-allowed_argument:

allowed
-------

..  confval:: allowed
    :name: fluidtypo3-flux-field-file-allowed
    :type: string
    :required: false

    Defines a list of file types allowed in this field

.. _fluidtypo3-flux-field-file-disallowed_argument:

disallowed
----------

..  confval:: disallowed
    :name: fluidtypo3-flux-field-file-disallowed
    :type: string
    :required: false

    Defines a list of file types NOT allowed in this field

.. _fluidtypo3-flux-field-file-uploadfolder_argument:

uploadFolder
------------

..  confval:: uploadFolder
    :name: fluidtypo3-flux-field-file-uploadfolder
    :type: string
    :required: false

    Upload folder to use for copied/directly uploaded files

.. _fluidtypo3-flux-field-file-showthumbnails_argument:

showThumbnails
--------------

..  confval:: showThumbnails
    :name: fluidtypo3-flux-field-file-showthumbnails
    :type: boolean
    :required: false

    If TRUE, displays thumbnails for selected values

.. _fluidtypo3-flux-field-file-usefalrelation_argument:

useFalRelation
--------------

..  confval:: useFalRelation
    :name: fluidtypo3-flux-field-file-usefalrelation
    :type: boolean
    :required: false

    Use a fal relation instead of a simple file path

.. _fluidtypo3-flux-field-file-internaltype_argument:

internalType
------------

..  confval:: internalType
    :name: fluidtypo3-flux-field-file-internaltype
    :type: string
    :Default: 'file_reference'
    :required: false

    Internal type (TCA internal_type) to use for the field. Defaults to `file_reference` but can be set to `file` to support file uploading
