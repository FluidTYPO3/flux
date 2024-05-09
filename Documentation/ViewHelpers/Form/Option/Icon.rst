..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/Option/IconViewHelper.php

:edit-on-github-link: Form/Option/IconViewHelper.php
:navigation-title: form.option.icon
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-option-icon:

=====================================================
Form.option.icon ViewHelper `<flux:form.option.icon>`
=====================================================

Icon option
===========

Sets the `icon` option in the Flux form, which can then be read by
extensions using Flux forms. Consult the documentation of extensions
which use the `icon` setting to learn more about how icons are used.

``value`` needs to be the absolute path to the image file, e.g.
``/typo3conf/ext/myext/Resources/Public/Icons/Element.svg``.

Example
-------

    <flux:form.option.icon value="/typo3conf/ext/myext/Resources/Public/Icons/Element.svg"/>

.. _fluidtypo3-flux-form-option-icon_source:

Source code
===========

Go to the source code of this ViewHelper: `IconViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/Option/IconViewHelper.php>`__.

.. _fluidtypo3-flux-form-option-icon_arguments:

Arguments
=========

The following arguments are available for `<flux:form.option.icon>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-option-icon-value_argument:

value
-----

..  confval:: value
    :name: fluidtypo3-flux-form-option-icon-value
    :type: string
    :required: false

    Path and name of the icon file
