.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-sheet:

==========
form.sheet
==========


FlexForm sheet ViewHelper

Groups FlexForm fields into sheets.

Arguments
=========


.. _form.sheet_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of the group, used as FlexForm sheet name, must be FlexForm XML-valid tag name string

.. _form.sheet_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for the field group - used as tab name in FlexForm. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.sheets.foobar" based on sheet name, in scope of extension rendering the Flux form.

.. _form.sheet_variables:

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

.. _form.sheet_description:

description
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional string or LLL reference with a desription of the purpose of the sheet

.. _form.sheet_shortdescription:

shortDescription
----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional shorter version of description of purpose of the sheet, LLL reference supported

.. _form.sheet_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
