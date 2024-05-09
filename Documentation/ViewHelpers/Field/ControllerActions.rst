..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Field/ControllerActionsViewHelper.php

:edit-on-github-link: Field/ControllerActionsViewHelper.php
:navigation-title: field.controllerActions
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-controlleractions:

===================================================================
Field.controllerActions ViewHelper `<flux:field.controllerActions>`
===================================================================

ControllerActions ViewHelper

Renders a FlexForm select field with options fetched from
requested extensionName/pluginName and other settings.

There are three basic ways of adding selection options:

- You can use the "extensionName" and "pluginName" to render all
  possible actions from an Extbase plugin that you've defined. It
  doesn't have to be your own plugin - if for example you are
  rendering actions from EXT:news or another through your own plugin.
- Or you can use the "actions" argument which is an array:
  {ControllerName: 'action1,action2,action3', OtherControllerName: 'action1'}
- And you can extend any of the two methods above with the "subActions"
  parameter, which allows you to extend the allowed actions whenever
  the specified combination of ControllerName + actionName is encountered.
  Example:       actions="{ControllerName: 'action1,action2'}"
                 subActions="{ControllerName: {action1: 'action3,action4'}}"
  Gives options: ControllerName->action1,action3,action4 with LLL values based on "action1"
                 ControllerName->action2 with LLL values based on "action2"
  By default Flux will create one option per action when reading
  Controller actions - using "subActions" it becomes possible to add
  additional actions to the list of allowed actions that the option
  will contain, as opposed to having only one action per option.

And there are a few ways to limit the options that are displayed:

- You can use "excludeActions" to specify an array in the same
  syntax used by the "actions" argument, these are then excluded.
- You can specifiy the "controllerName" argument in which case
  only actions from that Controller are displayed.

And there are a couple of ways to define/resolve labels for actions:

- You can add an LLL label in your locallang_db file:
  lowercasepluginname.lowercasecontrollername.actionfunctionname
  example index: myext.articlecontroller.show
- You can do nothing, in which case the very first line of
  the PHP doc-comment of each action method is used. This value can
  even be an LLL:file reference (in case you don't want to use the
  pattern above - but beware this is somewhat expensive processing)
- Or you can do nothing at all, not even add a doc comment, in which
  case the Controller->action syntax is used instead.

Marking actions that have required arguments (which cause errors if
rendered on a page that is accessible through a traditional menu) is
possible but is deactivated for LLL labels; if you use LLL labels
and your action requires an argument, be user friendly and note so
in the LLL label or docs as applies.

Lastly, you can set a custom name for the field in which case the
value does not trigger the Extbase SwitchableControllerActions feature
but instead works as any other Flux FlexForm field would.

To use the field just place it in your Flux form (but in almost all
cases leave out the "name" argument which is required on all other
field types at the time of writing this). Where the field is placed
is not important; the order and the sheet location don't matter.

.. _fluidtypo3-flux-field-controlleractions_source:

Source code
===========

Go to the source code of this ViewHelper: `ControllerActionsViewHelper.php (GitHub) <fluidtypo3/flux/development/Field/ControllerActionsViewHelper.php>`__.

.. _fluidtypo3-flux-field-controlleractions_arguments:

Arguments
=========

The following arguments are available for `<flux:field.controllerActions>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-controlleractions-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-controlleractions-name
    :type: string
    :Default: 'switchableControllerActions'
    :required: false

    Name of the field

.. _fluidtypo3-flux-field-controlleractions-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-controlleractions-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-field-controlleractions-default_argument:

default
-------

..  confval:: default
    :name: fluidtypo3-flux-field-controlleractions-default
    :type: string
    :required: false

    Default value for this attribute

.. _fluidtypo3-flux-field-controlleractions-native_argument:

native
------

..  confval:: native
    :name: fluidtypo3-flux-field-controlleractions-native
    :type: boolean
    :required: false

    If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _fluidtypo3-flux-field-controlleractions-position_argument:

position
--------

..  confval:: position
    :name: fluidtypo3-flux-field-controlleractions-position
    :type: string
    :required: false

    Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _fluidtypo3-flux-field-controlleractions-required_argument:

required
--------

..  confval:: required
    :name: fluidtypo3-flux-field-controlleractions-required
    :type: boolean
    :required: false

    If TRUE, this attribute must be filled when editing the FCE

.. _fluidtypo3-flux-field-controlleractions-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-controlleractions-exclude
    :type: boolean
    :required: false

    If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _fluidtypo3-flux-field-controlleractions-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-controlleractions-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-controlleractions-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-field-controlleractions-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, disables the field in the FlexForm

.. _fluidtypo3-flux-field-controlleractions-requestupdate_argument:

requestUpdate
-------------

..  confval:: requestUpdate
    :name: fluidtypo3-flux-field-controlleractions-requestupdate
    :type: boolean
    :required: false

    If TRUE, the form is force-saved and reloaded when field value changes

.. _fluidtypo3-flux-field-controlleractions-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-controlleractions-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-controlleractions-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-controlleractions-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-controlleractions-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-controlleractions-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-controlleractions-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-controlleractions-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-controlleractions-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-field-controlleractions-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-field-controlleractions-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-controlleractions-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-field-controlleractions-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-controlleractions-config
    :type: mixed
    :Default: array ()
    :required: false

    Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _fluidtypo3-flux-field-controlleractions-validate_argument:

validate
--------

..  confval:: validate
    :name: fluidtypo3-flux-field-controlleractions-validate
    :type: string
    :Default: 'trim'
    :required: false

    FlexForm-type validation configuration for this input

.. _fluidtypo3-flux-field-controlleractions-size_argument:

size
----

..  confval:: size
    :name: fluidtypo3-flux-field-controlleractions-size
    :type: integer
    :Default: 1
    :required: false

    Size of the selector box

.. _fluidtypo3-flux-field-controlleractions-multiple_argument:

multiple
--------

..  confval:: multiple
    :name: fluidtypo3-flux-field-controlleractions-multiple
    :type: boolean
    :required: false

    If TRUE, allows selecting the same value multiple times

.. _fluidtypo3-flux-field-controlleractions-minitems_argument:

minItems
--------

..  confval:: minItems
    :name: fluidtypo3-flux-field-controlleractions-minitems
    :type: integer
    :required: false

    Minimum required number of items to be selected

.. _fluidtypo3-flux-field-controlleractions-maxitems_argument:

maxItems
--------

..  confval:: maxItems
    :name: fluidtypo3-flux-field-controlleractions-maxitems
    :type: integer
    :Default: 1
    :required: false

    Maxium allowed number of items to be selected

.. _fluidtypo3-flux-field-controlleractions-itemliststyle_argument:

itemListStyle
-------------

..  confval:: itemListStyle
    :name: fluidtypo3-flux-field-controlleractions-itemliststyle
    :type: string
    :required: false

    Overrides the default list style when maxItems > 1

.. _fluidtypo3-flux-field-controlleractions-selectedliststyle_argument:

selectedListStyle
-----------------

..  confval:: selectedListStyle
    :name: fluidtypo3-flux-field-controlleractions-selectedliststyle
    :type: string
    :required: false

    Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _fluidtypo3-flux-field-controlleractions-items_argument:

items
-----

..  confval:: items
    :name: fluidtypo3-flux-field-controlleractions-items
    :type: mixed
    :required: false

    Optional, full list of items to display - note: if used, this overrides any automatic option filling!

.. _fluidtypo3-flux-field-controlleractions-emptyoption_argument:

emptyOption
-----------

..  confval:: emptyOption
    :name: fluidtypo3-flux-field-controlleractions-emptyoption
    :type: mixed
    :required: false

    If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _fluidtypo3-flux-field-controlleractions-translatecsvitems_argument:

translateCsvItems
-----------------

..  confval:: translateCsvItems
    :name: fluidtypo3-flux-field-controlleractions-translatecsvitems
    :type: boolean
    :required: false

    If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _fluidtypo3-flux-field-controlleractions-itemsprocfunc_argument:

itemsProcFunc
-------------

..  confval:: itemsProcFunc
    :name: fluidtypo3-flux-field-controlleractions-itemsprocfunc
    :type: string
    :required: false

    Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _fluidtypo3-flux-field-controlleractions-rendertype_argument:

renderType
----------

..  confval:: renderType
    :name: fluidtypo3-flux-field-controlleractions-rendertype
    :type: string
    :Default: 'selectSingle'
    :required: false

    Rendering type as applies in FormEngine/TCA

.. _fluidtypo3-flux-field-controlleractions-showicontable_argument:

showIconTable
-------------

..  confval:: showIconTable
    :name: fluidtypo3-flux-field-controlleractions-showicontable
    :type: boolean
    :required: false

    If TRUE shows the option icons as table beneath the select

.. _fluidtypo3-flux-field-controlleractions-controllerextensionname_argument:

controllerExtensionName
-----------------------

..  confval:: controllerExtensionName
    :name: fluidtypo3-flux-field-controlleractions-controllerextensionname
    :type: string
    :required: false

    Name of the Extbase extension that contains the Controller to parse, ex. MyExtension. In vendor based extensions use dot, ex. Vendor.MyExtension

.. _fluidtypo3-flux-field-controlleractions-pluginname_argument:

pluginName
----------

..  confval:: pluginName
    :name: fluidtypo3-flux-field-controlleractions-pluginname
    :type: string
    :required: false

    Name of the Extbase plugin that contains Controller definitions to parse, ex. MyPluginName

.. _fluidtypo3-flux-field-controlleractions-controllername_argument:

controllerName
--------------

..  confval:: controllerName
    :name: fluidtypo3-flux-field-controlleractions-controllername
    :type: string
    :required: false

    Optional extra limiting of actions displayed - if used, field only displays actions for this controller name - ex Article(Controller) or FrontendUser(Controller) - the Controller part is implied

.. _fluidtypo3-flux-field-controlleractions-actions_argument:

actions
-------

..  confval:: actions
    :name: fluidtypo3-flux-field-controlleractions-actions
    :type: mixed
    :Default: array ()
    :required: false

    Array of "ControllerName" => "csv,of,actions" which are allowed. If used, does not require the use of an ExtensionName and PluginName (will use the one specified in your current plugin automatically)

.. _fluidtypo3-flux-field-controlleractions-excludeactions_argument:

excludeActions
--------------

..  confval:: excludeActions
    :name: fluidtypo3-flux-field-controlleractions-excludeactions
    :type: mixed
    :Default: array ()
    :required: false

    Array of "ControllerName" => "csv,of,actions" which must be excluded

.. _fluidtypo3-flux-field-controlleractions-prefixonrequiredarguments_argument:

prefixOnRequiredArguments
-------------------------

..  confval:: prefixOnRequiredArguments
    :name: fluidtypo3-flux-field-controlleractions-prefixonrequiredarguments
    :type: string
    :Default: '*'
    :required: false

    A short string denoting that the method takes arguments, ex * (which should then be explained in the documentation for your extension about how to setup your plugins

.. _fluidtypo3-flux-field-controlleractions-disablelocallanguagelabels_argument:

disableLocalLanguageLabels
--------------------------

..  confval:: disableLocalLanguageLabels
    :name: fluidtypo3-flux-field-controlleractions-disablelocallanguagelabels
    :type: boolean
    :required: false

    If TRUE, disables LLL label usage and just uses the class comment or Controller->action syntax

.. _fluidtypo3-flux-field-controlleractions-locallanguagefilerelativepath_argument:

localLanguageFileRelativePath
-----------------------------

..  confval:: localLanguageFileRelativePath
    :name: fluidtypo3-flux-field-controlleractions-locallanguagefilerelativepath
    :type: string
    :Default: '/Resources/Private/Language/locallang_db.xml'
    :required: false

    Relative (from extension $extensionName) path to locallang file containing the action method labels

.. _fluidtypo3-flux-field-controlleractions-subactions_argument:

subActions
----------

..  confval:: subActions
    :name: fluidtypo3-flux-field-controlleractions-subactions
    :type: mixed
    :Default: array ()
    :required: false

    Array of sub actions {ControllerName: {list: 'update,delete'}, OtherController: {new: 'create'}} which ' .
           'are also allowed but not presented as options when the mapped action is selected (in example: if ' .
           'ControllerName->list is selected, ControllerName->update and ControllerName->delete are allowed - but ' .
           'cannot be selected).

.. _fluidtypo3-flux-field-controlleractions-separator_argument:

separator
---------

..  confval:: separator
    :name: fluidtypo3-flux-field-controlleractions-separator
    :type: string
    :required: false

    Separator string (glue) for Controller->action values, defaults to "->". Empty values result in default being used.
