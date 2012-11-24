TYPO3 extension Fluidcontent: Fluid Content Elements
====================================================

## What is it?

**Fluid Content** enables the use of special content elements, each based on a Fluid template - much like TemplaVoila's flexible
content elements. The feature was born in the extension FED and ported into this extension, making a very light extension with
a simple, highly specific purpose. It uses Flux to enable highly dynamic configuration of variables used when rendering the
template.

Two sets of built-in content elements are provided which can be included as needed:

* Common content elements such as a column divider, an AJAX content loader, a file list and a bread crumb element.
* Twitter Bootstrap content elements such as collapsibles, dismissable alert boxes, button groups, hero unit and navigation lists.

## What does it do?

EXT:fluidcontent lets you write custom content elements based on Fluid templates. Each content element and its possible settings
are contained in a single Fluid template file. Whole sets of files can be registered and placed in its own tab in the new content
element wizard, letting you group your content elements. The template files are placed in a very basic extension.

The _Nested Content Elements_ support that Flux enables is utilized to make content elements which can contain other content
elements - and which can be edited inline in the pgage backend module (with native drag and drop support in 6.0 and drag and drop
support in 4.x branches through the Grid Elements extension - key `gridelements`).

## Why use it?

**Fluid Content** is a fast, dynamic and extremely flexible way to create content elements. Not only can you use Fluid, you can
also create dynamic configuration options for each content element using Flux - in the exact same way as done in the Fluid Pages
extension; see https://github.com/NamelessCoder/fluidpages.

## How does it work?

Fluid Content Elements are registered through TypoScript. The template files are then processed to read various information about
each template, which is then made available for use just as any other content element type is used.

When editing the content element, Flux is used to generate the form section which lets content editors configure variables which
become available in the template. This allows completely dynamic variables (as opposed to adding extra fields on the tt_content
table and configuring TCA for each added column).

Content templates work best if they are shipped (and created) in an extension, the key of which is used by identify the content
templates in relation to the Fluid Content extension. This makes the templates excellently portable and allow you to quickly add
custom ViewHelpers used by your specific page templates. Such an extension need only contain an `ext_emconf.php` file and
optionally a static TypoScript configuration and an `ext_localconf.php` to register that TypoScript static configuration. Using
a static file makes it easy to include the content elements.

## How to include content element templates

Use the following TypoScript:

```
plugin.tx_fed.fce.myextension {
	templateRootPath = EXT:myextension/Resources/Private/Elements/
	partialRootPath = EXT:myextension/Resources/Private/Partials/
	layoutRootPath = EXT:myextension/Resources/Private/Layouts/
}
```

_Note: the `tx_fed` namespace is legacy from FED, it is still currently in use but will be replaced (but with backwards
compatibility preserved)_

**Fluid Content** emulates a `Content` object and an associated `ContentController` - but has a slightly modified behavior than
a traditional Extbase controller in that it __does not suffix the template path with the object name when looking for template
files__. This means that templates should be located in `EXT:myextension/Resources/Private/Elements/` if the
`plugin.tx_fed.fce.myextension.templateRootPath` setting is set to `EXT:myextension/Resources/Private/Elements/`. This differs
from traditional controllers which would be looking for files in `EXT:myextension/Resources/Private/Elements/Content/` given
that the object's name is `Content`.

Other than this, the templates all follow the usual Fluid rules regarding Layouts and Partials. When rendering your content
element the paths are used which are set in the collection to which the content element belongs.

## How to create content element templates

Templates follow the rules of regular Fluid templates and has just one additional requirement: each template file must be
provided with a `<f:section name="Configuration">` which contains the Flux configuration that applies to the content element.

### An example template

```xml
{namespace fed=Tx_Fed_ViewHelpers}
{namespace flux=Tx_Flux_ViewHelpers}
{namespace widget=Tx_Fluidwidget_ViewHelpers}

<f:layout name="Content" />
<div xmlns="http://www.w3.org/1999/xhtml" lang="en"
     xmlns:flux="http://fedext.net/ns/flux/ViewHelpers"
     xmlns:widget="http://fedext.net/ns/fluidwidget/ViewHelpers"
     xmlns:f="http://typo3.org/ns/fluid/ViewHelpers">

<f:section name="Configuration">
    <flux:flexform id="ajax-loader" label="Ajax Loader" description="Loads content through AJAX. Requires EXT:fluidwidget">
        <flux:flexform.field.checkbox name="settings.disable" label="Disable content loading" />
        <flux:flexform.grid>
            <flux:flexform.grid.row>
                <flux:flexform.grid.column>
                    <flux:flexform.content name="default" label="Content elements to load through AJAX" />
                </flux:flexform.grid.column>
            </flux:flexform.grid.row>
        </flux:flexform.grid>
    </flux:flexform>
</f:section>

<f:section name="Preview">
	<p>
		AJAX content loading <strong><f:if condition="{settings.disable}" then="DISABLED" else="ENABLED" /></strong>
	</p>
    <flux:widget.grid />
</f:section>

<f:section name="Main">
	<f:if condition="{settings.disable}">
		<f:else>
		    <widget:content.ajaxFluxContent parentUid="{record.uid}" area="default" />
		</f:else>
	</f:if>
</f:section>

</div>
```

### An explanation of the above template

Some facts about the above sample template:

* The `flux` namespace is used for configuration, the `widget` namespace for the AJAX loading Widget.
* A `<div>` wraps the entire template, allowing tag autocompletion and attribute validation (by associating XSD schemas to each
  namespace - see https://github.com/NamelessCoder/schemaker for more information about this feature).
* The `Content.html`  Layout file is used. It is allowed to render any section **except for the `Configuration` section**
* The `Configuration` section contains:
	* A `<flux:flexform>` node with the minimum allowed configuration: an ID unique to this file (among files in this same
	  extension) and a human-readable label presented to content editors when selecting page templates.
	* A `<flux:flexform.field.checkbox>` field allowing the AJAX loading to be switched off completely
	* A `<flux:flexform.grid>` with one row with one column, containing one content area allowing content editors to insert
	  the content elements which will be loaded through AJAX. __This grid is mandatory when using content areas__.
* A `Preview` section which uses Flux's grid Widget to render the actual nested content element area - this approach is used in
  every content element which can contain other content elements - and additional preview content, in this case just a short
  feedback message if AJAX is enabled or disabled. This section is not rendered from the Layout but from Flux when rendering
  Previews for content elements in the page backend module.
* The `Main` section which is rendered from the `Content` Layout and contains the actual frontend display of the content element.
  This particular element contains only a condition which can disable the AJAX loading and the Widget which performs the AJAX
  loading (Widget provided by EXT:fluidwidget - see https://github.com/NamelessCoder/fluidwidget).

## References

Other extensions which are either dependencies of or closely related to this extension:

* https://github.com/NamelessCoder/flux is a dependency and is used to configure how the content template variable are defined.
* https://github.com/NamelessCoder/vhs is a highly suggested companion for Fluid Content templates, providing useful ViewHelpers.
* https://github.com/NamelessCoder/fluidpages is a recommendation for a site built with Fluid, but is not TemplaVoila compatible.