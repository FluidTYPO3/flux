.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form:

====
form
====


FlexForm configuration container ViewHelper

Arguments
=========


.. _form_id:

id
--

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Identifier of this Flexible Content Element, `/[a-z0-9]/i` allowed.

.. _form_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for the form, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId", in scope of extension rendering the Flux form.

.. _form_description:

description
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Short description of the purpose/function of this form

.. _form_enabled:

enabled
-------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If FALSE, features which use this form can elect to skip it. Respect for this flag depends on the feature using the form.

.. _form_variables:

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

.. _form_options:

options
-------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Custom options to be assigned to Form object - valid values depends on the. See docs of extension in which you use this feature. Can also be set using `flux:form.option` as child of `flux:form`.

.. _form_locallanguagefilerelativepath:

localLanguageFileRelativePath
-----------------------------

:aspect:`DataType`
   string

:aspect:`Default`
   '/Resources/Private/Language/locallang.xlf'

:aspect:`Required`
   false
:aspect:`Description`
   Relative (from extension) path to locallang file containing labels for the LLL values used in this form.

.. _form_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
