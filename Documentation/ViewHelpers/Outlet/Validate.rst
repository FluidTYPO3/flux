.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-outlet-validate:

===============
outlet.validate
===============


ViewHelper to validate Outlet arguments

Use `<flux:outlet.validate>` inside the `<flux.outlet.argument>` viewHelper.
You can add any number of validations to the arguments. After submission
validation errors will be available inside the validationResults variable.

Example
=======

    <f:section name="Configuration">
         <flux:outlet>
              <flux:outlet.argument name="name">
                   <flux:outlet.validate type="NotEmpty" />
              </flux:outlet.argument>
         </flux:outlet>
    </f:section>

    <f:section name="Main">
        <f:form noCache="1">
            <f:form.textfield name="name" value="{name}" />
            <f:if condition="{validationResults.name}">
                <f:for each="{validationResults.name}" as="error">
                    <span class="error">{error.code}: {error.message}</span>
                </f:for>
            </f:if>
        </f:form>
    </f:section>

Arguments
=========


.. _outlet.validate_type:

type
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Validator to apply

.. _outlet.validate_options:

options
-------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Additional validator arguments
