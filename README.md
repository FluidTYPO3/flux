<img src="https://fluidtypo3.org/logo.svgz" width="100%" />

Flux
====

[![Coverage Status](https://img.shields.io/coveralls/FluidTYPO3/flux/development.svg?style=flat-square)](https://coveralls.io/r/FluidTYPO3/flux)  [![Documentation](http://img.shields.io/badge/documentation-online-blue.svg?style=flat-square)](https://fluidtypo3.org/documentation/templating-manual/introduction.html) [![Performance profiling sponsored by Blackfire](https://img.shields.io/badge/performance_profiling_sponsored_by-blackfire-red?style=flat-square)](https://blackfire.io/)

> Flux automates integration of Fluid with TYPO3 and makes Fluid the entry point for developers. The name "Flux" has
> multiple meanings but in this context, it mainly refers to the gel-like fluid used when soldering components together
> in electronic hardware, making it easier to create a bond between components and improves the durability of the bond. 

Flux has two main purposes:

1. Allow developers to configure TYPO3's page templates and add custom content types using Fluid templates, without
   the need for detailed knowledge about how TYPO3 usually requires such setup to be done.
2. Allow embedding of metadata such as which fields to show when editing a content element, descriptions of the
   content or page template, and more.
   
Bonus feature: nested content areas to create content grids.


Documentation
-------------

* [ViewHelper Reference for Flux](https://viewhelpers.fluidtypo3.org/fluidtypo3/flux/)
* [How to use the Flux APIs](https://fluidtypo3.org/documentation/templating-manual/templating/creating-templates/flux-fields.html)


How it works
------------

Flux has two main modes of operation - either you allow Flux to automatically create a site-wide "design" folder to
contain your template files, which provides a 100% out of the box experience. Or you prefer to have a more advanced and
controlled integration, in which case you disable the automatic creation of site-wide template folders and provide your 
own (through an extension).

The automatic mode of operation is the default. **This means Flux is an ideal starting point if you have zero knowledge
about how to set up TYPO3**. You can start with the automation and as you learn more about TYPO3, you can refine your
integration and continuously improve how it works. Or keep using the automation in case it fits all your needs.

To get started you only need to know how to install a TYPO3 extension either with Composer or for non-Composer sites,
using the Extension Manager.


Types of integration
--------------------

Flux has multiple ways to store your page- and content templates (which are also type definitions):

1. Fully automated through the site-wide "design" folder in the public root. This integration is easiest to use but
   forces your templates to render in Flux's context, and does not facilitate complex setups such as extension specific
   asset storage, and is not as portable as other methods - on the other hand it works immediately out of the box.
   Ideal for simple single-domain sites and gets you started in just a few minutes.
2. Extension-specific, meaning you use a TYPO3 extension (written by yourself) to store all page- and content templates
   and additional resources such as translations, icons, and so on. This method is the most portable, meaning you can
   package your entire site templating in an extension that you can reuse, and is the only method which allows you to
   also ship PHP classes (ViewHelpers, for example) along with your site templates.
3. For content types specifically, you can define new types using root-level database records (one per type). This
   method is possible to use completely without touching the file system, but has a lesser degree of portability. You
   can use this method to quickly create prototypes of content types using a more visual approach (through TYPO3 forms).
   This method also has an exporting feature which allows you to generate the necessary Fluid template that can then be
   used with either of the other methods.

Methods 1 and 3 are intended to get you started as quickly as possible. Method 2 is intended to serve more custom setups
which ship more than just site templates, through use of a custom TYPO3 extension.

All three methods can be combined or used individually.


Composer Install
----------------

**Recommended!**

```bash
composer req fluidtypo3/flux
./vendor/bin/typo3 extension:install flux
# alternatively, instead of extension:install, activate in Extension Manager backend module
```

Non-Composer Install
--------------------

**NOT recommended!**

1. In the Extension Manager backend module, search for `flux`
2. Choose to install the result with the extension key `flux`


Setup
-----

* There is no required setup to use Flux content types (but you almost certainly need to install `fluid_styled_content`
  to be able to render any content at all).
* To use page templates without a content grid (which assumes you defined a grid with pageTSconfig or other) you only
  need to select the template to use in `Page Layout` when editing a page (start with the top page).
* If your page template additionally contains a grid, you must also select `Columns from selected "Page Layout"` as the
  value of the two `Backend Layout` fields in the `Appearance` tab. 

The remaining setup of labels, form fields, grid composition etc. can all be done from within your Fluid templates.


How does it work?
-----------------

When Flux is installed and enabled in extension manager, and if automatic creation of site-wide Flux templates is
enabled (which it is by default), the following happens automatically:

* A folder named `design` is created in the public directory (this directory may differ between TYPO3 versions and can
  be changed with configuration, but in most recent TYPO3 versions it is `public` in the project public folder).
* This folder is filled with a set of skeleton templates containing very basic embedded Flux metadata.
* The file created in `design/Templates/Page` can be selected as page template (Flux adds a `Page Layout` tab to pages'
  editable properties).
* The file created in `design/Templates/Content` becomes a custom content type which can be inserted just like the
  standard TYPO3 content types that create text, image, etc.

Renaming, removing or adding files in these folders automatically registers the file as either page template or content
type, depending on location. **Be careful when renaming or removing files: if the page- or content template is already
in use this may break your system until you choose another page template and disable/delete removed or renamed content
types. There is no warning given about types that are in use!**

From that point on, you can create a completely styled site with custom content types to make sliders etc. using your
favorite frontend framework (or none) - and you only need to know very basic Fluid (an XML based markup engine which
comes with automatically rendered documentation for every tag you can use).


What does it NOT do?
--------------------

Flux does not remove the need to learn "the TYPO3 way" of doing things - you should still aim to learn more about how
TYPO3 works. Flux only makes it quicker to get started and provides a reasonable level of automation; complex sites will
almost surely still require you to learn a bit about TYPO3 (such as, how to modify the `<meta>` section and how to use
third party plugins for news etc.)

Flux is also not a replacement for things like `fluid_styled_content` (although it can work without it) - Flux creates
custom content types, it does not replace TYPO3's native content types (although you can hide those and use only your
custom types).

Lastly, Flux only has limited abstraction over how you define form fields. To know all the specific details of what each
type of field does, you still need to know TYPO3's "TCA" (which is thoroughly documented). Flux tries as far as possible
to use the same names of form field attributes as TCA. If you don't understand an attribute or aren't sure which field
type to use, always consult the TCA documentation (keeping in mind not all field types will work: Flux fields are based
on FlexForm fields. When FlexForm does not support a field type it is noted so in the TCA documentation).


Recommendation of VHS
---------------------

VHS is another extension in the FluidTYPO3 family, which is highly recommended to use along with Flux. The reason VHS is
mentioned here, is that it provides alternatives to TypoScript-based content- and menu-rendering instructions, allowing
you to instead use Fluid.

Given that in particular menu rendering setup in TypoScript is notoriously difficult (due to a very old structure which
has basically never changed), beginners may prefer to use a special XHTML tag and either a few CSS class properties, or
a custom loop to output menu items and their links.


Flux form API
-------------

Flux lets you build and modify forms in Fluid, which become form fields in the form that edits content/page properties
through the TYPO3 backend:

```xml
<flux:form id="myform">
  <flux:field.input name="myField" label="My special field" />
</flux:form>
```

Flux also lets you build a grid for content elements (nested content areas):

```xml
<flux:grid>
  <flux:grid.row>
    <flux:grid.column colPos="0" name="main" label="Main content area" />
  </flux:grid.row>
</flux:form>
```

Flux is then capable of extracting these embedded structures to read form fields, labels, content grids, backend preview
output, and more - in short, your template files embed the instructions on how to both integrate and render templates. 


Alternative APIs
----------------

As you create more complex projects they usually have more complex requirements - which may still benefit from Flux
features such as a way to create Flux forms for custom plugins. Since Flux works by increasingly abstracting the API of
TYPO3 core features (with the Fluid "flavor" as the most condensed and abstracted) Flux also declares these increasingly
flexible layers of abstraction as public API.

This means Flux also has a good old PHP way to declare forms and so on:

```php
$form = \FluidTYPO3\Flux\Form::create();
$form->setName('myform');
$form->createField('Input', 'myField', 'My special field');
```

And supports plain arrays (to allow sources like JSON):

```php
$json = '{name: "myform", fields: [{"name": "myField", "type": "Input"}]}';
$asArray = json_decode($json, JSON_OBJECT_AS_ARRAY);
$form = \FluidTYPO3\Flux\Form::create($asArray);
```

And can use TypoScript:

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

Field Value Data Transformation
-------------------------------

Flux is capable of transforming the values of fields before assigning the values as template variables. You can use this to
ensure that the variables in your templates have the correct types - and you can also use it as a quick way to transform
for example a selected foreign record to an instance of the domain model. Consider the following example:

```xml
<flux:form id="myform">
  <flux:field.input name="myField" label="My special field" transform="boolean" />
</flux:form>
```

When Flux renders the template that contains this form, the variable `{myField}` will be converted to a proper boolean.

The following transformation types are built-in:

* `string`
* `integer` (alias: `int`)
* `boolean` (alias: `bool`)
* `array` (will explode value by commas, e.g. `a,b,c` becomes `['a', 'b', 'c']`)
* `float` (alias: `decimal` and `double`)
* `file`
* `filereference`
* `files`
* `filereferences`

The `file` etc. types will convert file references added through an IRRE FAL field to either a `File` or `FileReference`
object, or an array of such objects if you use the plural forms.

You can also specify a class name of a Domain Model as the `transform` type. Flux will then attempt to use the matching
Repository to load a single instance of that Domain Model. The value saved in the DB must be an integer `uid` of the
related object (for example, the `flux:field.relation` field type will save such a value). If you need multiple instances
from a multiple selection field (like `flux:field.multiRelation`) you must use a compound transform type like:

```xml
transform="\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\My\Extension\Domain\Model\MyObject>"
```

Or you can use a custom container class instead of ObjectStorage, for example if you have an Iterator that sorts objects
in a certain way. The container class will receive an array of the domain model instances as first constructor argument.

```xml
transform="\My\Extension\Iterator\SpecialSorter<\My\Extension\Domain\Model\MyObject>"
```

Or you can specify any class which takes a constructor argument. Flux will then create an instance of the specified class
with the database value as constructor argument. For example, using `\DateTime` as transform type and having a value of
`now` in the database results in a `DateTime` instance with the current date+time:

```xml
transform="\DateTime"
```

Finally you can also create an implementation of `FluidTYPO3\Flux\Form\Transformation\DataTransformerInterface` which
can support any type of data transformation:

```php
<?php
namespace My\Extension\Transformation;

use FluidTYPO3\Flux\Attribute\DataTransformer;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\Transformation\DataTransformerInterface;

/* PHP 8.0 registration with class attribute. ID must be unique! */
#[DataTransformer('myextension.datatransformer.mytransform')]
class MyTransformer implements DataTransformerInterface
{
    public function canTransformToType(string $type): bool
    {
        // Support transform of any type that begins with "myextension:"
        return strpos($type, 'myextension:') === 0;
    }

    public function getPriority(): int
    {
        // Higher priority means your DataTransformer is checked before others with lower priority.
        return 10;
    }

    /**
     * @var FieldInterface|ContainerInterface $component
     * @var mixed $value
     * @return mixed
     */
    public function transform(FormInterface $component, string $type, $value)
    {
        $converted = null;
        switch ($type) {
            case 'myextension:foo':
                $converted = 'call something to transform $value to one type';
                break;
            case 'myextension:bar':
                $converted = 'call something else to transform $value to another type';
                break;
        }
        return $converted;
    }
}
```

The above class would support both `transform="myexternsion:foo"` and `transform="myextension:bar` through the same class.
Obviously a DataTransformer can also support just a single type.

You can override other DataTransformers or give priority to your own DataTransformer if it supports the same type as other
transformers, by returning a higher number from the `getPriority` method.

**Special note about PHP 7.4**

Since PHP 7.4 does not support attributes like PHP 8.0 and above, you'll need to manually specify a DI tag for your class,
in `Configuration/Services.yaml` of your extension:

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  My\Extension\:
    resource: '../Classes/*'

  My\Extension\Transformation\MyTransformer:
    tags:
      - name: flux.datatransformer
        identifier: 'myextension.datatransformer.mytransform'
```

This is not necessary on PHP 8.0+ because the DI container will scan for the `#[DataTransformer('id')]` attribute and
automatically tag your class as a DataTransformer for Flux.


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


Caveats
-------

* Keep In mind to have your PHP/HTTP configured correctly to accept a fairly large number of input fields. When nesting
  sections / objects the number of fields submitted, rises drastically. The `php.ini` configuration setting to think about is
  `max_input_vars`. If this number is too small then the TYPO3 Backend (being PHP) will decline the submission of the
  backend editing form and will exit with an "Invalid CSRF Token" message because of incomplete (truncated) `POST` data.
* When working with custom Provider classes: 1) make sure you declare each Provider class as `public: true` in Service
  config of your extension. And 2) if the Provider needs to be used for more than one specific page/content/record type,
  make sure you also declare the Provider as `shared: false`. You can find an example of such configuration in the
  `Configuration/Services.yaml` file in Flux.
* When working with custom Controller classes, make sure you declare each Controller class as `public: true` in Service
  config of your extension.
* Note that both Providers and Controllers use constructor injection of dependencies. If you use custom Controllers and/or
  Providers and these override the `__construct` method, make sure that they take the same arguments as the parent class and
  that you call `parent::__construct()` with those arguments from your overridden `__construct` method.
