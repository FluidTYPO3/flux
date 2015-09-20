# Flux Change log

7.2.2 - 2015-09-20
------------------

Solid round of bug fixes mainly, few added minor (and fully backwards compatible) features and focus on fixes for drag-and-drop
and copy-paste behavior on TYPO3 6.2. Some minor fixes for workspaces support.

Notable changes/features:

1. Template paths now use `0` as default index instead of previous `10`. This *can* have an adverse effect on sites that for some
   reason has one or more sets of templates which: **a)** are configured by replacing the default `10` index, AND, **b)** have
   removed one or more templates that exist in the original path. Such a setup *may begin to display previously disabled template
   files as selectable options in the backend*. To fix this, update your TypoScript template paths to use `0` as the bottom
   priority paths.
2. Icons now support SVG files. Not much to say about this one - use an `.svg` file as icon for a template, either through the
   `icon` Form option or by placing it in the convention-based expected icon path.
3. Form now supports `sorting` as a global option that can be used by any implementation; previously `fluidcontent` added its own
   but can now delegate this to Flux. This means that from now on you should define `options="{sorting: 10}"` instead of wrapping
   the sorting value in a scope like `{Fluidcontent: {sorting: 10}}`.
4. When rendering Requests and when retrieving Form instances from templates, Flux will now respect the `vendorName` request
   parameter - and will in fact pass-through the original Request (as a cloned instance) whenever an original Request exists.
   Though this change doesn't have any effect on the surface, it does improve frontend rendering scope consistency when rendering
   through a custom Flux controller.
5. TYPO3 7.4 is now supported.

List of all changes affecting users:

- Bugfix for record drag-and-drop in anticipation of TYPO3 7.5
  - https://github.com/FluidTYPO3/flux/commit/73eac6aa58d2e9993a0e67a9c5fa2a2cb9fad700
- Bugfix for correctly generating `emptyOption` on `flux:form.select` and enabling the use of the property on ViewHelpers
  - https://github.com/FluidTYPO3/flux/commit/22ccceebc6222290ddafc016b9107539501e73ae
  - https://github.com/FluidTYPO3/flux/commit/1f395ed808e3a90e49a9508cf571eb657d13e662
- Added "localize" buttons for content containers
  - https://github.com/FluidTYPO3/flux/commit/7c67a554cb55db19832d48261d71be0a9471be62
- Added support for calling Pipes of Form's Outlet when TYPO3 executes a command on record associated with Provider and Form
  - https://github.com/FluidTYPO3/flux/commit/c1aa12a0fbbf5b1fca32fb2a3338f547cc654af4
- Added support for `matchFields` (TCA `MM_match_fields`) on `flux:form.select` and other relation components
  - https://github.com/FluidTYPO3/flux/commit/2437d01e91f4740bfac6e794991e7d0914d5be4b
  - https://github.com/FluidTYPO3/flux/commit/e4672a40ef29da1c62792498fd4159290485b7b3
- Permit `getPreview` to throw error if template file is configured but does not exist or reference is incorrect
  - https://github.com/FluidTYPO3/flux/commit/4f7986af83257f4a5eef6ca2347c36155c72768c
- Remove default "Flux" controller name when reading Form etc. - no longer applies; invoking extensions' controller name now used.
  - https://github.com/FluidTYPO3/flux/commit/fb81da16f3a31fe65c981514af0d493fc570ca5b
- Bugfix to only show non-child content records in list view. Content elements placed inside Flux areas now not rendered.
  - https://github.com/FluidTYPO3/flux/commit/ecc9fc718e2138637671f76f7c516daf0c6c3891
  - https://github.com/FluidTYPO3/flux/commit/c0864b4f02ccd7382146e6f15737bf3576d2ed0e
- Bugfix for Provider not returning template path and filename unless prefixed with `EXT:` - now allows absolute paths
  - https://github.com/FluidTYPO3/flux/commit/e850702166911f663ecd04fe20b530c25a50542b
- Bugfix for warnings caused by removing/uninstalling an extension while configuration still exists
  - https://github.com/FluidTYPO3/flux/commit/cc1cc293655da1de8b9a2e8383a65e1025975ed4
- Bugfix for warnings caused by copying a record with an already-extracted set of Flux values
  - https://github.com/FluidTYPO3/flux/commit/088bccea79f0f9ca075e53003e6ea254cebf8673
- Fixes for resolving action of Flux controllers and presence of controller classes
  - https://github.com/FluidTYPO3/flux/commit/eb2abb03e12fa5d367854d580fbeb33908520e9d
- Full passthrough of original Requests (as clone) for all Flux implementations - full control over execution scope from outside
  - https://github.com/FluidTYPO3/flux/commit/c3d4939cbc462c946f179191f550ee05c3d78b7c
- Bugfix to allow vendor name to be set in Requests transferred to Flux controllers
  - https://github.com/FluidTYPO3/flux/commit/4b728c10da8fc7d96d2b3ed6384498c2434d627d
- FlashMessages (unsafe on TYPO3 7.4+) replaced with syslog messages
  - https://github.com/FluidTYPO3/flux/commit/57a579a0f50fa1226e193e5927fbb3191b06c52a
- Bugfix to respect "Show hidden elements" checkbox in page layout view
  - https://github.com/FluidTYPO3/flux/commit/d287a5b50702bbf19e55823dffd08b233da24ed6
  - https://github.com/FluidTYPO3/flux/commit/737c4a92b99ff95d3b94603a6a61a2c797910e23
- Translate (overlay with localised version) page values that are available as `{page}` in template
  - https://github.com/FluidTYPO3/flux/commit/10e40dbd58ec1f182b5cc084fd6b0cd46e0e6d42
- SVG support for icons
  - https://github.com/FluidTYPO3/flux/commit/cc874e49cdad1c0e18f930e23e6bd43a751f138e
- Bugfix for editing child records in a non-live workspace
  - https://github.com/FluidTYPO3/flux/commit/123d0113ecadfe829c3b815441b099921326c395
- Form option `sorting` is now supported as global option to indicate sorting order of resulting Form objects
  - https://github.com/FluidTYPO3/flux/commit/dee62c5ff38330663336e7854b0f08f63acf105a
- Bugfix/performance during Provider resolving when no provider is resolved
  - https://github.com/FluidTYPO3/flux/commit/1c544f7683b45106923c47b23668c4c0c34ebebf
  - https://github.com/FluidTYPO3/flux/commit/d35827c39ac7d554737518b5d41aedd32cfc03ae
- Bugfix for value of `tx_flux_parent` in translated versions of records
  - https://github.com/FluidTYPO3/flux/commit/e8f6835aa6b1c8604de279ad0d60a0d73d3fe20c
- Parent/area now transferred correctly when using "save and add new after" inside a Flux content area
  - https://github.com/FluidTYPO3/flux/commit/e20471f4d744dddc81e14a8e530af8ba34673266
- Bugfix for ContentProvider to trigger on every `tt_content` record - ensures copy/paste handling triggers
  - https://github.com/FluidTYPO3/flux/commit/bbe0a21bdea7163c6b8f82ec3ba6f4447b537ee4
- Inheritance control arguments implemented in ViewHelper API of Section and Object
  - https://github.com/FluidTYPO3/flux/commit/8028910da83a439754904fe6b907168e8aa394a8
- Bugfix to disallow pasting an element as child of itself (preventing endless loop)
  - https://github.com/FluidTYPO3/flux/commit/377255637deb6574693e2a70e314cd35cff3e2b3
- Bugfix for grid rendering in Preview section
  - https://github.com/FluidTYPO3/flux/commit/2ac737a04aa3c60facfa55ead3a936dc1b184a47
- Bugfixes for icon supports
  - [https://github.com/FluidTYPO3/flux/commit/76751fa13cd1c088a42df45108f5f83c3db01382](Icon generation utility feature)
  - https://github.com/FluidTYPO3/flux/commit/3d7f480ccd8099a96db5cb082e620b34c9edac67
  - https://github.com/FluidTYPO3/flux/commit/3ee1bd81a737f308a9573b70323b478c670c18c7
- :exclamation: Template paths use `0` as default index instead of `10`, to prevent confusion
  - https://github.com/FluidTYPO3/flux/commit/707e0c1f00c41981457d8c295013eb551c70b69c
- Bugfixes for drag-and-drop / copy-paste support
  - https://github.com/FluidTYPO3/flux/commit/5ca68331dca6abd9339c24f13e7e0b3fdfa5e4d9
  - https://github.com/FluidTYPO3/flux/commit/7b128294804ec9df61dfe8466642e016a0e9db8b
  - https://github.com/FluidTYPO3/flux/commit/bd4b46e10d4fc3dc5bd95c3bd4a2f2c1df0e78fe
  - https://github.com/FluidTYPO3/flux/commit/b920849a701be93a6781a9788202ce62b791a855
  - https://github.com/FluidTYPO3/flux/commit/5b4bf945b36bf2eb2337344ca94edb0e7c826d50
- Bugfixes to clear out stored data if all data is empty
  - https://github.com/FluidTYPO3/flux/commit/327de57b2ff4216cbe54696eed4b58ea6a13f831
  - https://github.com/FluidTYPO3/flux/commit/c0a970fb36f855f467f06d9b6769b9f0f665b76f
- Bugfix for objects used inside sections in forms not using the right parent
  - https://github.com/FluidTYPO3/flux/commit/824b1ea7bf743b7679e38a05bbf975e7615350f8
- Bugfix to avoid rendering Preview section when template file is missing/unresolved.
  - https://github.com/FluidTYPO3/flux/commit/7d0993ec2a32d618102692a80f15e07d9df2fa56

7.2.1 - 2015-05-20
------------------

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
