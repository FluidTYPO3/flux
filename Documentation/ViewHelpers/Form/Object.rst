:navigation-title: form.object
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-object:

===========================================
form.object ViewHelper `<flux:form.object>`
===========================================


FlexForm field section object ViewHelper

Use this inside flux:form.section to name and divide the fields
into individual objects that can be inserted into the section.


.. _fluidtypo3-flux-form-object_arguments:

Arguments
=========


.. _form.object_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of the section object, FlexForm XML-valid tag name string

.. _form.object_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for section object, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.objects.foobar" based on object name, in scope of extension rendering the Flux form.

.. _form.object_variables:

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

.. _form.object_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _form.object_inherit:

inherit
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _form.object_inheritempty:

inheritEmpty
------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _form.object_transform:

transform
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _form.object_contentcontainer:

contentContainer
----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, each object that is created of this type results in a content column of the same name, with an automatic colPos value.
