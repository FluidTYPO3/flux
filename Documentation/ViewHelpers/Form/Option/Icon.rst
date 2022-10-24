.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-option-icon:

================
form.option.icon
================


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

Arguments
=========


.. _form.option.icon_value:

value
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Path and name of the icon file
