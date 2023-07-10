.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-section:

============
form.section
============


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

Arguments
=========


.. _form.section_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of the attribute, FlexForm XML-valid tag name string

.. _form.section_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for section, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.sections.foobar" based on section name, in scope of extension rendering the form.

.. _form.section_variables:

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

.. _form.section_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _form.section_inherit:

inherit
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _form.section_inheritempty:

inheritEmpty
------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _form.section_gridmode:

gridMode
--------

:aspect:`DataType`
   string

:aspect:`Default`
   'rows'

:aspect:`Required`
   false
:aspect:`Description`
   Defines how section objects which are marked as content containers, get rendered as a grid. Valid values are either "rows" or "columns". Default is to render as rows.
