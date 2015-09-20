<img src="https://fluidtypo3.org/logo.svgz" width="100%" />

Flux: Fluid FlexForms
=====================

[![Build Status](https://img.shields.io/travis/FluidTYPO3/flux.svg?style=flat-square&label=package)](https://travis-ci.org/FluidTYPO3/flux/) [![Coverage Status](https://img.shields.io/coveralls/FluidTYPO3/flux/development.svg?style=flat-square)](https://coveralls.io/r/FluidTYPO3/flux)  [![Documentation](http://img.shields.io/badge/documentation-online-blue.svg?style=flat-square)](https://fluidtypo3.org/documentation/templating-manual/introduction.html) [![Build Status](https://img.shields.io/travis/FluidTYPO3/fluidtypo3-testing.svg?style=flat-square&label=framework)](https://travis-ci.org/FluidTYPO3/fluidtypo3-testing/) [![Coverage Status](https://img.shields.io/coveralls/FluidTYPO3/fluidtypo3-testing/master.svg?style=flat-square)](https://coveralls.io/r/FluidTYPO3/fluidtypo3-testing)

> Flux is a replacement API for TYPO3 FlexForms - with interfaces for Fluid, PHP and TypoScript

Flux lets you build and modify forms in Fluid:

```xml
<flux:form id="myform">
  <flux:field.input name="myField" label="My special field" />
</flux:form>
```

In PHP:

```php
$form = \FluidTYPO3\Flux\Form::create();
$form->setName('myform');
$form->createField('Input', 'myField', 'My special field');
```

In plain arrays (to allow sources like JSON):

```php
$json = '{name: "myform", fields: [{"name": "myField", "type": "Input"}]}';
$asArray = json_decode($json, JSON_OBJECT_AS_ARRAY);
$form = \FluidTYPO3\Flux\Form::create($asArray);
```

And in TypoScript:

```plain
plugin.tx_flux.providers {
  myextension_myplugin {
    tableName = tt_content
    fieldName = pi_flexform
    listType = myextension_myplugin
    extensionKey = Vendor.MyPlugin
    form {
      name = myform
      fields {
        myField {
          type = Input
          label = My special field
        }
      }
    }
  }
}
```

All of which create the same form with a single input field called `myField` with a label value of `My special field`. The last
example shows the `form` structure nested in a Provider (another Flux concept) which connects the `pi_flexform` field of the
related `tt_content` plugin record type to the form.

Flux feature highlights
-----------------------

* Added features for content elements - add content grids (following the `backend_layout` approach) to any content/plugin.
* Multiple APIs to access the same features from many different contexts using the same naming and nesting style.
* Multiple levels of API abstraction - when you need more control, lower API abstraction levels can be used in your code.
* Flexible ways to replace individual parts: templates, controller actions, etc.
* Manipulation of properties of existing forms - change field labels, default values, add fields, sheets, etc.
* Data type transformations - define the desired target type and let the TypeConverters of Extbase handle conversion.
* Possibility for custom components of your own - with the same API support any other Flux component has.
* Several Utility-type classes for advanced integrations with Fluid in particular.

Known issues
------------

* Keep In mind to have your PHP/HTTP configured correctly to accept a fairly large number of input fields. When nesting
  sections / objects the number of fields submitted, rises drastically. The `php.ini` configuration setting to think about is
  `max_input_vars`. If this number is too small then the TYPO3 Backend (being PHP) will decline the submission of the
  backend editing form and will exit with an "Invalid CSRF Token" message because of incomplete (truncated) `POST` data.

Documentation
-------------

* [ViewHelper Reference for Flux](https://fluidtypo3.org/viewhelpers/flux.html)
* [How to use the Flux APIs](https://fluidtypo3.org/documentation/templating-manual/templating/creating-templates/flux-fields.html)
