:navigation-title: form.object.columnPosition
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-object-columnposition:

=========================================================================
form.object.columnPosition ViewHelper `<flux:form.object.columnPosition>`
=========================================================================


Section object - automatic colPos field

Provides a field to designate the "colPos" value of
a section object, which is automatically maintained
and guaranteed uniqueness when editing the backend
form. Adding this field inside `<flux:field.object />`
renders a field that's not user editable which contains
a unique colPos value for each section object.

The colPos field is then associated with the section
object _and will not change even if the section object
is moved up or down in the section_.

This property can then be used in `<flux:grid.column />`
arguments if the grid columns are created by iterating
the section objects and creating a column for each.

Example
-------

      <flux:form id="sectionobjectasgrid" options="{static: 1}" extensionName="FluidTYPO3.TestProviderExtension">
          <flux:form.sheet name="options">
              <flux:form.section name="columns">
                  <flux:form.object name="column" label="Column">
                      <flux:form.object.columnPosition />
                  </flux:form.object>
              </flux:form.section>
          </flux:form.sheet>
      </flux:form>
      <flux:grid>
          <flux:grid.row>
              <f:for each="{columns}" as="columnObject">
                  <flux:grid.column colPos="{columnObject.column.colPos}" />
              </f:for>
          </flux:grid.row>
      </flux:grid>

Notes
-----

Please be aware that dynamic grid is NOT compatible
with the "static" option for `<flux:form />` - this
option must not be enabled; if it is, the grid will
not be rendered.


.. _fluidtypo3-flux-form-object-columnposition_arguments:

Arguments
=========


This ViewHelper has no arguments.
