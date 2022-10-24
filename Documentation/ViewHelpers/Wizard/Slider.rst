.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-wizard-slider:

=============
wizard.slider
=============


Field Wizard: Slider

See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
for details about the behaviors that are controlled by arguments.

DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes

Arguments
=========


.. _wizard.slider_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'Slider'

:aspect:`Required`
   false
:aspect:`Description`
   Optional title of this Wizard

.. _wizard.slider_hideparent:

hideParent
----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, hides the parent field

.. _wizard.slider_variables:

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

.. _wizard.slider_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _wizard.slider_step:

step
----

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Increment slider values by this much for each step

.. _wizard.slider_width:

width
-----

:aspect:`DataType`
   integer

:aspect:`Default`
   32

:aspect:`Required`
   false
:aspect:`Description`
   Width of the popup window
