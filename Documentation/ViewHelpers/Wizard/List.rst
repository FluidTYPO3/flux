.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-wizard-list:

===========
wizard.list
===========


Field Wizard: List

See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
for details about the behaviors that are controlled by arguments.

DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes

Arguments
=========


.. _wizard.list_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional title of this Wizard

.. _wizard.list_hideparent:

hideParent
----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, hides the parent field

.. _wizard.list_variables:

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

.. _wizard.list_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _wizard.list_table:

table
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Table name that records are added to

.. _wizard.list_pid:

pid
---

:aspect:`DataType`
   mixed

:aspect:`Default`
   '###CURRENT_PID###'

:aspect:`Required`
   false
:aspect:`Description`
   Storage page UID or (as is default) ###CURRENT_PID###

.. _wizard.list_width:

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

.. _wizard.list_height:

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
