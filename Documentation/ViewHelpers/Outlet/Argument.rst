.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-outlet-argument:

===============
outlet.argument
===============


ViewHelper to define Outlet arguments

Use `<flux:outlet.argument>` in conjunction with the `<flux:outlet>` and `<flux.outlet.validate>` viewHelpers.
You can define any number of arguments including validations that will be applied to the outlet action.
To call the outlet action use the action "outlet" in your form action.

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
        <f:form action="outlet" noCache="1">
            <f:form.textfield name="name" value="{name}" />
        </f:form>
    </f:section>

Arguments
=========


.. _outlet.argument_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of the argument

.. _outlet.argument_type:

type
----

:aspect:`DataType`
   string

:aspect:`Default`
   'string'

:aspect:`Required`
   false
:aspect:`Description`
   Type of the argument
