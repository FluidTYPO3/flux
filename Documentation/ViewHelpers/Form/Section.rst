..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/SectionViewHelper.php

:edit-on-github-link: Form/SectionViewHelper.php
:navigation-title: form.section
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-section:

=============================================
Form.section ViewHelper `<flux:form.section>`
=============================================

FlexForm field section ViewHelper

Using a section to let a user add many elements
-----------------------------------------------

    <flux:form.section name="settings.numbers" label="Telephone numbers">
        <flux:form.object name="mobile" label="Mobile">
            <flux:field.input name="number"/>
        </flux:form.object>
        <flux:form.object name="landline" label="Landline">
            <flux:field.input name="number"/>
        </flux:form.object>
    </flux:form.section>

Reading section element values
------------------------------

    <f:for each="{settings.numbers}" as="obj" key="id">
        Number #{id}:
        <f:if condition="{obj.landline}">mobile, {obj.landline.number}</f:if>
        <f:if condition="{obj.mobile}">landline, {obj.mobile.number}</f:if>
        <br/>
    </f:for>

.. _fluidtypo3-flux-form-section_source:

Source code
===========

Go to the source code of this ViewHelper: `SectionViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/SectionViewHelper.php>`__.

.. _fluidtypo3-flux-form-section_arguments:

Arguments
=========

The following arguments are available for `<flux:form.section>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-section-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-form-section-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-form-section-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-form-section-label
    :type: string
    :required: false

    Label for section, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.sections.foobar" based on section name, in scope of extension rendering the form.

.. _fluidtypo3-flux-form-section-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-form-section-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-form-section-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-form-section-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-form-section-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-form-section-inherit
    :type: boolean
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-form-section-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-form-section-inheritempty
    :type: boolean
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-form-section-gridmode_argument:

gridMode
--------

..  confval:: gridMode
    :name: fluidtypo3-flux-form-section-gridmode
    :type: string
    :Default: 'rows'
    :required: false

    Defines how section objects which are marked as content containers, get rendered as a grid. Valid values are either "rows" or "columns". Default is to render as rows.
