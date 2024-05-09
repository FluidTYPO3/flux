..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/ContainerViewHelper.php

:edit-on-github-link: Form/ContainerViewHelper.php
:navigation-title: form.container
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-container:

=================================================
Form.container ViewHelper `<flux:form.container>`
=================================================

FlexForm Field Container element
================================

Use around other Flux fields to make these fields nested visually
and in variable scopes (i.e. a field called "name" inside a palette
called "person" would end up with "person" being an array containing
the "name" property, rendered as {person.name} in Fluid.

The field grouping can be hidden or completely removed. In this regard
this element is a simpler version of the Section and Object logic.

Grouping elements with a container
----------------------------------

    <flux:form.container name="settings.name" label="Name">
        <flux:field.input name="firstname" label="First name"/>
        <flux:field.input name="lastname" label="Last name"/>
    </flux:form.container>

Accessing values of grouped elements
------------------------------------

    Name: {settings.name.firstname} {settings.name.lastname}

.. _fluidtypo3-flux-form-container_source:

Source code
===========

Go to the source code of this ViewHelper: `ContainerViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/ContainerViewHelper.php>`__.

.. _fluidtypo3-flux-form-container_arguments:

Arguments
=========

The following arguments are available for `<flux:form.container>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-container-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-form-container-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-form-container-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-form-container-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-form-container-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-form-container-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-form-container-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-form-container-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
