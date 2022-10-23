.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-wizard-colorpicker:

==================
wizard.colorPicker
==================


Field Wizard: Color Picker

See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
for details about the behaviors that are controlled by arguments.

DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes

Arguments
=========


.. _wizard.colorpicker_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'Choose color'

:aspect:`Required`
   false
:aspect:`Description`
   Optional title of this Wizard

.. _wizard.colorpicker_hideparent:

hideParent
----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, hides the parent field

.. _wizard.colorpicker_variables:

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

.. _wizard.colorpicker_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _wizard.colorpicker_dim:

dim
---

:aspect:`DataType`
   string

:aspect:`Default`
   '20x20'

:aspect:`Required`
   false
:aspect:`Description`
   Dimensions (WxH, e.g. 20x20) of color picker

.. _wizard.colorpicker_width:

width
-----

:aspect:`DataType`
   integer

:aspect:`Default`
   450

:aspect:`Required`
   false
:aspect:`Description`
   Width of the popup window

.. _wizard.colorpicker_height:

height
------

:aspect:`DataType`
   integer

:aspect:`Default`
   720

:aspect:`Required`
   false
:aspect:`Description`
   Height of the popup window

.. _wizard.colorpicker_exampleimg:

exampleImg
----------

:aspect:`DataType`
   string

:aspect:`Default`
   'EXT:flux/Resources/Public/Icons/ColorWheel.png'

:aspect:`Required`
   false
:aspect:`Description`
   Example image from which to pick colors
