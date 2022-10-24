.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-wizard-link:

===========
wizard.link
===========


Field Wizard: Link

Link input field with link wizard
---------------------------------

    <flux:field.input name="link">
        <flux:wizard.link/>
    </flux:field.input>

See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
for details about the behaviors that are controlled by arguments.

DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes

Arguments
=========


.. _wizard.link_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'Select link'

:aspect:`Required`
   false
:aspect:`Description`
   Optional title of this Wizard

.. _wizard.link_hideparent:

hideParent
----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, hides the parent field

.. _wizard.link_variables:

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

.. _wizard.link_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _wizard.link_activetab:

activeTab
---------

:aspect:`DataType`
   string

:aspect:`Default`
   'file'

:aspect:`Required`
   false
:aspect:`Description`
   Active tab of the link popup

.. _wizard.link_width:

width
-----

:aspect:`DataType`
   integer

:aspect:`Default`
   500

:aspect:`Required`
   false
:aspect:`Description`
   Width of the popup window

.. _wizard.link_height:

height
------

:aspect:`DataType`
   integer

:aspect:`Default`
   500

:aspect:`Required`
   false
:aspect:`Description`
   Height of the popup window

.. _wizard.link_allowedextensions:

allowedExtensions
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Comma-separated list of extensions that are allowed to be selected. Default is all types.

.. _wizard.link_blindlinkoptions:

blindLinkOptions
----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Blind link options

.. _wizard.link_blindlinkfields:

blindLinkFields
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Blind link fields
