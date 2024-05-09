..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/Option/InheritanceModeViewHelper.php

:edit-on-github-link: Form/Option/InheritanceModeViewHelper.php
:navigation-title: form.option.inheritanceMode
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-option-inheritancemode:

===========================================================================
Form.option.inheritanceMode ViewHelper `<flux:form.option.inheritanceMode>`
===========================================================================

Inheritance mode option
=======================

Control how this Form will handle inheritance (page context only).
There are two possible values of this option:

- restricted
- unrestricted

Note that the default (the mode which is used if you do NOT specify
the mode with this ViewHelper/option) is defined by the Flux extension
configuration. If you do not change the extension configuration then
the default behavior is "restricted". Any template that wants to use
a mode other than the default *MUST* specify the mode with this option.

When the option is set to "restricted" either by this ViewHelper or
by extension configuration, the inheritance behavior matches the
Flux behavior pre version 10.1.x, meaning that inheritance will only
happen if the parent (page) has selected the same Form (layout) as
the current page. As soon as a different Form is encountered in a
parent, the inheritance stops. In short: inheritance only works for
identical Forms.

Alternatively, when the option is set to "unrestricted", the above
constraint is removed and inheritance can happen for Forms which are
NOT the same.

This makes sense to use if you have different page templates which
use the same values (for example a shared set of fields) and you want
child pages to be able to inherit these values from parents even if
the child page has selected a different page layout.

Example
-------

    <flux:form.option.inheritanceMode value="unrestricted" />
    (which is the same as:)
    <flux:form.option.inheritanceMode>unrestricted</flux:form.option.inheritanceMode>

Or:

    <flux:form.option.inheritanceMode value="restricted" />
    (which is the same as:)
    <flux:form.option.inheritanceMode>restricted</flux:form.option.inheritanceMode>

.. _fluidtypo3-flux-form-option-inheritancemode_source:

Source code
===========

Go to the source code of this ViewHelper: `InheritanceModeViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/Option/InheritanceModeViewHelper.php>`__.

.. _fluidtypo3-flux-form-option-inheritancemode_arguments:

Arguments
=========

The following arguments are available for `<flux:form.option.inheritanceMode>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-option-inheritancemode-value_argument:

value
-----

..  confval:: value
    :name: fluidtypo3-flux-form-option-inheritancemode-value
    :type: string
    :required: false

    Mode of inheritance, either "restricted" or "unrestricted".
