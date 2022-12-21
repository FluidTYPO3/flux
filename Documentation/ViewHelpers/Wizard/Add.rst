.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-wizard-add:

==========
wizard.add
==========


Field Wizard: Add

See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
for details about the behaviors that are controlled by arguments.

DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes

Arguments
=========


.. _wizard.add_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'Add new record'

:aspect:`Required`
   false
:aspect:`Description`
   Optional title of this Wizard

.. _wizard.add_hideparent:

hideParent
----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, hides the parent field

.. _wizard.add_variables:

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

.. _wizard.add_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _wizard.add_table:

table
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Table name that records are added to

.. _wizard.add_pid:

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

.. _wizard.add_setvalue:

setValue
--------

:aspect:`DataType`
   string

:aspect:`Default`
   'prepend'

:aspect:`Required`
   false
:aspect:`Description`
   How to treat the record once created
