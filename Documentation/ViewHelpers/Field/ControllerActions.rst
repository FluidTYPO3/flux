:navigation-title: field.controllerActions
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-controlleractions:

===================================================================
field.controllerActions ViewHelper `<flux:field.controllerActions>`
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


.. _fluidtypo3-flux-field-controlleractions_arguments:

Arguments
=========


.. _field.controlleractions_name:

name
----

:aspect:`DataType`
   string

:aspect:`Default`
   'switchableControllerActions'

:aspect:`Required`
   false
:aspect:`Description`
   Name of the field

.. _field.controlleractions_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _field.controlleractions_default:

default
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Default value for this attribute

.. _field.controlleractions_native:

native
------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _field.controlleractions_position:

position
--------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _field.controlleractions_required:

required
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this attribute must be filled when editing the FCE

.. _field.controlleractions_exclude:

exclude
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _field.controlleractions_transform:

transform
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _field.controlleractions_enabled:

enabled
-------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If FALSE, disables the field in the FlexForm

.. _field.controlleractions_requestupdate:

requestUpdate
-------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the form is force-saved and reloaded when field value changes

.. _field.controlleractions_displaycond:

displayCond
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _field.controlleractions_inherit:

inherit
-------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _field.controlleractions_inheritempty:

inheritEmpty
------------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _field.controlleractions_clear:

clear
-----

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _field.controlleractions_protect:

protect
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, a "protect value" checkbox is displayed next to the field which when checked, protects the value from being changed if the (normally inherited) field value is changed in a parent record. Has no effect if "inherit" is disabled on the field.

.. _field.controlleractions_variables:

variables
---------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _field.controlleractions_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _field.controlleractions_config:

config
------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _field.controlleractions_validate:

validate
--------

:aspect:`DataType`
   string

:aspect:`Default`
   'trim'

:aspect:`Required`
   false
:aspect:`Description`
   FlexForm-type validation configuration for this input

.. _field.controlleractions_size:

size
----

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Size of the selector box

.. _field.controlleractions_multiple:

multiple
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows selecting the same value multiple times

.. _field.controlleractions_minitems:

minItems
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Minimum required number of items to be selected

.. _field.controlleractions_maxitems:

maxItems
--------

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Maxium allowed number of items to be selected

.. _field.controlleractions_itemliststyle:

itemListStyle
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default list style when maxItems > 1

.. _field.controlleractions_selectedliststyle:

selectedListStyle
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _field.controlleractions_items:

items
-----

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Optional, full list of items to display - note: if used, this overrides any automatic option filling!

.. _field.controlleractions_emptyoption:

emptyOption
-----------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _field.controlleractions_translatecsvitems:

translateCsvItems
-----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _field.controlleractions_itemsprocfunc:

itemsProcFunc
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _field.controlleractions_rendertype:

renderType
----------

:aspect:`DataType`
   string

:aspect:`Default`
   'selectSingle'

:aspect:`Required`
   false
:aspect:`Description`
   Rendering type as applies in FormEngine/TCA

.. _field.controlleractions_showicontable:

showIconTable
-------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE shows the option icons as table beneath the select

.. _field.controlleractions_controllerextensionname:

controllerExtensionName
-----------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of the Extbase extension that contains the Controller to parse, ex. MyExtension. In vendor based extensions use dot, ex. Vendor.MyExtension

.. _field.controlleractions_pluginname:

pluginName
----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of the Extbase plugin that contains Controller definitions to parse, ex. MyPluginName

.. _field.controlleractions_controllername:

controllerName
--------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional extra limiting of actions displayed - if used, field only displays actions for this controller name - ex Article(Controller) or FrontendUser(Controller) - the Controller part is implied

.. _field.controlleractions_actions:

actions
-------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Array of "ControllerName" => "csv,of,actions" which are allowed. If used, does not require the use of an ExtensionName and PluginName (will use the one specified in your current plugin automatically)

.. _field.controlleractions_excludeactions:

excludeActions
--------------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Array of "ControllerName" => "csv,of,actions" which must be excluded

.. _field.controlleractions_prefixonrequiredarguments:

prefixOnRequiredArguments
-------------------------

:aspect:`DataType`
   string

:aspect:`Default`
   '*'

:aspect:`Required`
   false
:aspect:`Description`
   A short string denoting that the method takes arguments, ex * (which should then be explained in the documentation for your extension about how to setup your plugins

.. _field.controlleractions_disablelocallanguagelabels:

disableLocalLanguageLabels
--------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, disables LLL label usage and just uses the class comment or Controller->action syntax

.. _field.controlleractions_locallanguagefilerelativepath:

localLanguageFileRelativePath
-----------------------------

:aspect:`DataType`
   string

:aspect:`Default`
   '/Resources/Private/Language/locallang_db.xml'

:aspect:`Required`
   false
:aspect:`Description`
   Relative (from extension $extensionName) path to locallang file containing the action method labels

.. _field.controlleractions_subactions:

subActions
----------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Array of sub actions {ControllerName: {list: 'update,delete'}, OtherController: {new: 'create'}} which ' .
           'are also allowed but not presented as options when the mapped action is selected (in example: if ' .
           'ControllerName->list is selected, ControllerName->update and ControllerName->delete are allowed - but ' .
           'cannot be selected).

.. _field.controlleractions_separator:

separator
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Separator string (glue) for Controller->action values, defaults to "->". Empty values result in default being used.
