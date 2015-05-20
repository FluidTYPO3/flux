# Flux Change log

7.2.1 - Upcoming
-----------------

- Bugfixes for moving of records when using `css_styled_content` and Flux.
  - https://github.com/FluidTYPO3/flux/commit/5296b426cce2ce92f7eb7c2d77792425d3c284ad

- Bugfixes for behavior or localisation (translated record would remain in original position).
  - https://github.com/FluidTYPO3/flux/commit/7a541dd5777a2db7a11347034a438594ecf3e0f0

- Bugfix for resolving of active page UID in environments with multiple root TypoScript templates.
  - https://github.com/FluidTYPO3/flux/commit/d7d62803f40d96a36ce8098d21d350da723fff82

- Bugfix for PHP error when viewing uncached frontend without an active backend user login.
  - https://github.com/FluidTYPO3/flux/commit/1ed3939e8b534d256c5d335e4e31a407f08cd9be

- A deprecated Wizard configuration has been corrected, restoring operability on TYPO3 7.1 and above.
  - https://github.com/FluidTYPO3/flux/commit/3f6512e9904b9ff1c9a8df0d4ad2dc55294540a3

7.2.0 - 2015-03-18
------------------

Flux has undergone a lot of maintenance work and optimisations. The main goal has been to increase performance and remove
bottlenecks, and to make the Flux API simpler and more consistent to use. A lot of legacy support has been removed and the
existing support for template paths has been improved, bringing it completely into sync with the TYPO3 core. Overall, Flux
now uses much more of the TYPO3 core's code to do the job especially concerning the View aspect.

- Full TYPO3 7.1.0 support.

- Full TYPO3 6.2.0 support (including new composer autoloader feature).

- :exclamation: Legacy namespace support completely removed
  - It is no longer possible to use any of Flux classes by their legacy names. Switch to the proper vendor and namespace.

- Support for disabling the Fluid template compiler was removed. The TYPO3 `Development` context plus file monitoring now works.

- [Custom Form classes can now be resolved by naming convention](https://github.com/FluidTYPO3/flux/commit/720da8eeeb1aa31bcbaf905f2a129be22d0ed5b8)
  - Convention: `^Classes/Form/{$controllerName}/{$action}Form`

- [Records' additional data now rendered in nested columns](https://github.com/FluidTYPO3/flux/commit/62724fea18fb34cf2a2fee7e73ecd37c6075fd2e)
  - Access restrictions, start- and end-time are now displayed in a row below each content, if they are defined.

- [Template path definitions were harmonised](https://github.com/FluidTYPO3/flux/commit/a2f42897da44c332aaf4e920a9229a1c7f69624f)
  - Multiple paths are supported using the `templateRootPaths` (plural) namings for all paths.
  - Template paths can now be defined using any of the known namings, but:
  - The `templateRootPath` and `overlays.xyz.templateRootPath` namings are deprecated and support will be removed in another two versions.

- [All Form components now support the `enabled` attribute](https://github.com/FluidTYPO3/flux/commit/9a02b011e356a7b2e7928780094f2c9cbc6d4030)
  - Default value is `TRUE` - value can be changed to `FALSE` to make the component not be rendered.

- :exclamation: [Inheritance support removed from Provider base class](https://github.com/FluidTYPO3/flux/commit/50bb0d0d56cf974bc9729d3dff8a426693efa7f1)
  - The inheritance feature is moved to Fluidpages to limit complexity of Flux itself.
  - If your custom Provider relied on inheritance and was not already extending the PageProvider of Fluidpages, extend it now to restore inheritance.

- [Overrides of all Flux form values became possible through TypoScript](https://github.com/FluidTYPO3/flux/commit/8fe7e6efd1e60e81f5281e65d77343c7edf177d1)

- [Icons associated with Flux forms are displayed in page module](https://github.com/FluidTYPO3/flux/commit/9f519ae743628ee795059c38823301ea4f4b5354)
  - The icons can be seen in the page module's content overview as well as in list mode.
  - The icons (selected page template) of pages can be seen in list view.

- [Icons can now be resolved by name convention](https://github.com/FluidTYPO3/flux/pull/687)
  - Convention: `^Resources/Public/Icons/{$controllerName}/{$actionName}.(gif|png)`

- [The `multiRelation` field type was added](https://github.com/FluidTYPO3/flux/commit/f25c708bd0b55a15319a8dc365672be68845e002)
  - The field type that creates the `group` TCEforms field type was added.
  - https://fluidtypo3.org/viewhelpers/flux/master/Field/MultiRelationViewHelper.html

- [Additional ViewHelpers added to define Form options](https://github.com/FluidTYPO3/flux/commit/1a8e2698940e49609efa31ab73045cee1750f8f6)
  - Rather than using the `options` property on `flux:form`, a set of `flux:form.option` and `flux:form.option.*` ViewHelpers are added.
  - https://fluidtypo3.org/viewhelpers/flux/master/Form/OptionViewHelper.html
  - https://fluidtypo3.org/viewhelpers/flux/master/Form/Option/GroupViewHelper.html
  - https://fluidtypo3.org/viewhelpers/flux/master/Form/Option/IconViewHelper.html

- [All Flux ViewHelpers can now use the `extensionName` argument to switch context](https://github.com/FluidTYPO3/flux/commit/a9ec0b14c54f770f47b0cb30e6992090fa0684e1)
  - You can set this attribute in for example an overridden partial template containing Flux components.
  - Overriding the `extensionName` makes LLL values and other automatically resolved values be resolved from that extension.
  - The original `extensionName` argument on `flux:field.controllerActions` has been renamed to `controllerExtensionName` because of this.
  - [Background info](https://github.com/FluidTYPO3/flux/issues/722)
