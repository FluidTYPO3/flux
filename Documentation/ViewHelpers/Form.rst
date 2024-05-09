..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/FormViewHelper.php

:edit-on-github-link: FormViewHelper.php
:navigation-title: form
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form:

=============================
Form ViewHelper `<flux:form>`
=============================

FlexForm configuration container ViewHelper

.. _fluidtypo3-flux-form_source:

Source code
===========

Go to the source code of this ViewHelper: `FormViewHelper.php (GitHub) <fluidtypo3/flux/development/FormViewHelper.php>`__.

.. _fluidtypo3-flux-form_arguments:

Arguments
=========

The following arguments are available for `<flux:form>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-id_argument:

id
--

..  confval:: id
    :name: fluidtypo3-flux-form-id
    :type: string
    :required: true

    Identifier of this Flexible Content Element, `/[a-z0-9]/i` allowed.

.. _fluidtypo3-flux-form-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-form-label
    :type: string
    :required: false

    Label for the form, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId", in scope of extension rendering the Flux form.

.. _fluidtypo3-flux-form-description_argument:

description
-----------

..  confval:: description
    :name: fluidtypo3-flux-form-description
    :type: string
    :required: false

    Short description of the purpose/function of this form

.. _fluidtypo3-flux-form-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-form-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, features which use this form can elect to skip it. Respect for this flag depends on the feature using the form.

.. _fluidtypo3-flux-form-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-form-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-form-options_argument:

options
-------

..  confval:: options
    :name: fluidtypo3-flux-form-options
    :type: mixed
    :required: false

    Custom options to be assigned to Form object - valid values depends on the. See docs of extension in which you use this feature. Can also be set using `flux:form.option` as child of `flux:form`.

.. _fluidtypo3-flux-form-locallanguagefilerelativepath_argument:

localLanguageFileRelativePath
-----------------------------

..  confval:: localLanguageFileRelativePath
    :name: fluidtypo3-flux-form-locallanguagefilerelativepath
    :type: string
    :Default: '/Resources/Private/Language/locallang.xlf'
    :required: false

    Relative (from extension) path to locallang file containing labels for the LLL values used in this form.

.. _fluidtypo3-flux-form-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-form-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
