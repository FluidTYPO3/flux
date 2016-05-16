# Flux Change log

7.4.0 - 2016-05-16
------------------

Minor release with a couple new features:
 
- The Flux Package API which allows a simple class to be implemented and control how Flux is integrated (which controllers your
  provider extension supports, the default template paths it uses, and more). See https://github.com/FluidTYPO3/flux/pull/1038.
- Flux form `options` can now be defined as dotted paths, for exampel `<flux:form.option name="FluidContent.sorting" value="100" />`.
  See https://github.com/FluidTYPO3/flux/pull/1042.
- Flux provider extensions can now be registered for any feature with a single command - filename conventions and detection then
  determine which features get enabled. See https://github.com/FluidTYPO3/flux/commit/1e379247567c0f94921de0f82be9dc5a638f5091.

And a very important bug fix:

- Bugfix for editing IRRE relations in a FlexForm, applying to both new and existing elements (workaround for TYPO3 issue below)
  - https://github.com/FluidTYPO3/flux/commit/6508a5fb26af1511ab5d6309d7e6ffffcc222f1d

Many other bug fixes are included for breaking changes in FormEngine.

Other bug fixes and minor updates:

- Bugfix for `getTranslateCsvItems` for select form field
  - https://github.com/FluidTYPO3/flux/commit/05c9e5a2e88a6119b20031b05b9c6f7ed3ee448b
- `showIconTable` support for select form field and subtypes
  - https://github.com/FluidTYPO3/flux/commit/fd8a9e955234318a17d8bf0d65f6859b79db577e
- Prevent `realpath` on resolved template files (prevents issues on Windows)
  - https://github.com/FluidTYPO3/flux/commit/f24706612cd233de0d97263eb65782ddc7bf43f0
- Optimisations and bug fixes for form's LLL
  - https://github.com/FluidTYPO3/flux/commit/41cb134fe6a2ce2bac05fd04152ab5c392ceaba9
  - https://github.com/FluidTYPO3/flux/commit/dc9ca0a3ec0e0e3cc472a1828e9ca3a5d99ce9f2
  - https://github.com/FluidTYPO3/flux/commit/1aa7d60de1e18f68ebc73811412f27f2367b27c0
- `emptyOption` support for all relation type form fields
  - https://github.com/FluidTYPO3/flux/commit/27d5d81c0ec705b81c1695c957fbf26e4d66ed8d
- Radio field type added
  - https://github.com/FluidTYPO3/flux/commit/c838efcb8f9072bf1996db771716ede539238bad
- Legacy template path name supports removed
  - https://github.com/FluidTYPO3/flux/commit/843f52fbea4bd8b290ddb4b7aae19985a2976fb3
- Bugfix to avoid `range` in TCA of input fields if min/max not set
  - https://github.com/FluidTYPO3/flux/commit/706f35cb729ff4a01156c988a54726ad9b846705
- Bugfix for LLL path of default sheet on Windows systems
  - https://github.com/FluidTYPO3/flux/commit/ac152d8d5bcf593a409f7dfee636f91bb015290b
- Bugfix for missing `section=1` in TCA structure
  - https://github.com/FluidTYPO3/flux/commit/efc0bfc90648d279e5e05326e54fb722dfe9a015
- Bugfix for correcting language UID in preview section
  - https://github.com/FluidTYPO3/flux/commit/41d15dbb2827747dec6f03ecf7560a966f2af8f4
- Bugfix for parameter order in VariableViewHelper
  - https://github.com/FluidTYPO3/flux/commit/88ca4c8d69db1fda0947f044401013f7dea93179
- Bugfix to fall back to page context unaware ConfigurationManager when incorrect implementation is registered
  - https://github.com/FluidTYPO3/flux/pull/1094
- Bugfix for resolving page/parent relations in workspace mode in nested elements
  - https://github.com/FluidTYPO3/flux/commit/8efe39dd9a9c3e85b809d485c858f6ae7cf7036e
  - https://github.com/FluidTYPO3/flux/commit/affce839d04557e9c3df696a05e29f2594b691e0
- Improvements for label handling (to deliver untranslated references and let TYPO3 do the translation, allows things like
  Fluidcontent element labels to become translatable)
  - https://github.com/FluidTYPO3/flux/commit/81bb392f99ab1e30c30303ee5d885a7a44c9ff6f
  - https://github.com/FluidTYPO3/flux/commit/718365c98bdf552ba81f1cb05a564e6c67aa7c78
- Bugfix for list view hook in TYPO3v7
  - https://github.com/FluidTYPO3/flux/pull/1067
- Bugfix for translation handling
  - https://github.com/FluidTYPO3/flux/pull/1066
- Bugfix for resolving page ID in ConfigurationManager
  - https://github.com/FluidTYPO3/flux/pull/1065
- Option/argument to set `useFalRelation` on `flux:field.file` (use case: forced generation of non-FAL, legacy, file reference)
  - https://github.com/FluidTYPO3/flux/pull/1058
- PHP 7, TYPO3v8 and Fluid standalone compatibility
  - https://github.com/FluidTYPO3/flux/commit/07696ccc06589ae0c6cefaf40b7e1b85f889d3b2
  - https://github.com/FluidTYPO3/flux/commit/1d819e9b0b0b57c3717bf03409ddbd534f5d9393
  - https://github.com/FluidTYPO3/flux/commit/d26cbdc5345b6efe7a543e45e627d4d7c06eba4f
  - https://github.com/FluidTYPO3/flux/commit/7565ebcce55daa04b128e9569946a7b4d745301a
  - https://github.com/FluidTYPO3/flux/commit/496649953f54771d37c67ce31d71a5c87befe848
  - https://github.com/FluidTYPO3/flux/commit/104744fd8fe382c0fdf5744f7aca9331879e2e7e
- Update for `flux:form.render` to work with new FormEngine
  - https://github.com/FluidTYPO3/flux/commit/3a582939acff5d401c9ddc945a25eac1a01a6675
- Bugfix for issues with form components becoming children of themselves (infinite recursion / timeout)
  - https://github.com/FluidTYPO3/flux/commit/93afbf08c6a71183d90fcc20cd6bf7588ecc7b46
- Bugfix for respecting plugin signature when detecting controllers
  - https://github.com/FluidTYPO3/flux/pull/1043
- Bugfix for `foreignSelector` initial value
  - https://github.com/FluidTYPO3/flux/pull/1037
- `renderType` support as required by new FormEngine:
  - https://github.com/FluidTYPO3/flux/commit/f0e74d313f740c280e8b79508acc885aebfb0358
  - https://github.com/FluidTYPO3/flux/pull/1034

7.3.0 - 2015-11-20
------------------

New minor release introducing **TYPO3 7.6 LTS compatibility** - which also means that **from this point onward, the minimum
supported TYPO3 version is 7.6**. For those that still require critical fixes but must remain on for example TYPO3 6.2 we provide
a `legacy` branch: https://github.com/FluidTYPO3/flux/commits/legacy

However, some notes about the `legacy` branch:

1. We Provide this branch as-is, not guaranteeing compatibility.
2. We do not actively maintain this branch:
   * We happily accept suggestions for fixes including code
   * We happily accept pull requests to the legacy branch (but please observe our contribution guidelines very closely when making
     patches for this branch - we aim for a minimal maintenance effort)
3. There is no expected maximum lifetime of the branch, but you should prioritise upgrading your TYPO3 site to LTS as soon as you
   can. The 6.2 branch of TYPO3 no longer receives bug fixes (including security patches).

The following new changes and features are highlighted:

All ViewHelpers compilable
==========================

This change means that all Flux ViewHelpers can now be compiled to native PHP which increases the performance, in particular for
templates that have many instances, such as page templates.

However, this change has required a small change to the internal API of Flux: the `getComponent` method on Form component
ViewHelpers is now `static` which may yield warnings if you use custom component ViewHelpers - and depending on your PHP version.
Very few should be affected since custom components are rare, only causes warnings and only warns on newer versions of PHP.

Compatibility Registry
======================

A special registry has been introduced to facilitate easy version based dependency configuration. Essentially it allows you to
provide a list of TYPO3 versions and values that apply to that version, with resolving happening in a way that the maximum viable
configuration always gets returned. For example you can specify class names to return for TYPO3 versions 7.4 and 7.6, and if the
active version is 7.5 the 7.4-specific class name gets returned (because the 7.6-specific one cannot be used).

	\FluidTYPO3\Flux\Utility\CompatibilityRegistry::register(
    	'MyVendor\MyExtension\MyClass',
    	array(
    		'7.4.0' => 'MyVendor\MyExtension\Legacy\MyClass',
    		'7.6.0' => 'MyVendor\MyExtension\MyClass'
    	)
    );
    \FluidTYPO3\Flux\Utility\CompatibilityRegistry::get('MyVendor\MyExtension\MyClass');
    
The compatibility registry is introduced to make version checks completely uniform and allow any number of alternatives to be
speficied, consistently returning a single value without you having to care about checking TYPO3 versions. In addition, the
static signature means you can use the registry from anywhere (and manipulate it without mocking from unit tests).

On-the-fly TCA manipulation
===========================

Provider classes are fitted with a method that together with the new FormEngine allows TCA to be manipulated freely. If a Provider
is triggered when editing a record, every aspect of the editing form's composition can be manipulated. To utilise this feature all
you need to do is implement `public function processTableConfiguration(array $row, array $configuration)` in your Provider class
and make the method return the (modified) `$configuration` array. The `$configuration` array is a *big* array of FormEngine
configuration and is at the time of writing this not fully documented. See the official TYPO3 documentation for more information
about the FormEngine configuration - or debug the array and make your own experiments, it's not too difficult except for the size.

Various other changes
=====================

- ViewHelper `flux:field.tree.category` added as shorthand to configure a `sys_category` relation field
  - https://github.com/FluidTYPO3/flux/commit/e9ecf239e9c8038b5a497f606d8f68861fcab5de
- Bugfix to preserve and merge `$this->settings` from other controller when rendering sub-requests
  - https://github.com/FluidTYPO3/flux/commit/d34d2aaa4079e1ceebab4614321b6fa303a90a31
- Compatibility for use with standalone Fluid as dependency
  - https://github.com/FluidTYPO3/flux/commit/94f7e9323f60c22b752ea68008907ab73dc32417
- Bugfix for passing arguments of original request to foreign controller
  - https://github.com/FluidTYPO3/flux/commit/976d4497bbea97b57568875a7a3c12cc5a736d77
- Feature to specify default values associated with a `flux:form` - can be consumed by other features for any purpose
  - https://github.com/FluidTYPO3/flux/commit/bb9da3815e24f0a90aaeab65e35f2db9fee86820
- Bugfix for visibility of sorting arrows in list view and assignment of correct permissions used for visibility of action links
  - https://github.com/FluidTYPO3/flux/commit/ca6e6018610a00a52b1e885a8c0cdc202dd81e87
- Bugfix for cleaning of stored data
  - https://github.com/FluidTYPO3/flux/commit/5eaa1df584e033f51feea2ad02c8ffa803a7e5d4
- Feature to allow Provider classes to manipulate TCA on-the-fly
  - https://github.com/FluidTYPO3/flux/commit/e1af87651266b736b961dcd3a9ed96e0410efdbb
- Feature to toggle display of nested content elements in list view (use may have unexpected results but is widely requested)
  - https://github.com/FluidTYPO3/flux/commit/fab20a070409112b04c38c94c8b7ef69352fa72f
- Bugfix for moving elements from within nested content to top of page column (regression)
  - https://github.com/FluidTYPO3/flux/commit/7e20161288e6a830565e288bab91e908ca196ea3
- Bugfix for default value resolving of `foreignUnique` field
  - https://github.com/FluidTYPO3/flux/commit/090ac5c1cdf2adfd3177167ebd1934d7cbd8f574
- Bugfix to ensure correct calling of Provider commands for each DataHandler command provided
  - https://github.com/FluidTYPO3/flux/commit/744e53c8c1d0baa8f85207292db42eaf42b896db
- Styling fixes for 7.6 compatibility
  - https://github.com/FluidTYPO3/flux/commit/ecfcd4c41a12dffd4c73787e93a9487bfb172837
- Legacy (6.2) compatibility classes and functions removed
  - https://github.com/FluidTYPO3/flux/commit/956b8136befcff94a04c51b040113318a9ab98a7
  - https://github.com/FluidTYPO3/flux/commit/548f3b7345a48dd8633b249524bad6e7fb0d4dcc
- Various bug fixes and adaptations for compatibility with FormEngine
  - https://github.com/FluidTYPO3/flux/commit/1805b7dc06f0767948d546e6079aaec0fc99ea46
  - https://github.com/FluidTYPO3/flux/commit/e4489aaed867f27f4a6bfa32b50e5fbe015d2b33
  - https://github.com/FluidTYPO3/flux/commit/c9357b718818569e171eba2a588a979a6335fcca
- Feature to translate `<flux:field.select />` items lists provided as CSV, using naming convention
  https://github.com/FluidTYPO3/flux/commit/c5c79ea3f6b42c5abe48b24fab0a96d326cbc7df
- Bugfix for custom icon position
  - https://github.com/FluidTYPO3/flux/commit/f8923b61b768468ca991e5255df967ac5d1f762b
- Bugfix to clean empty element lists from stored data
  - https://github.com/FluidTYPO3/flux/commit/a047b0dfdabd52e693006dcabcbe11cc08afdc6b
- Bugfix for using root TS templates on separate page root lines
  - https://github.com/FluidTYPO3/flux/commit/f953dbfb4d163349555e96eb77949a57899d4abb
  - https://github.com/FluidTYPO3/flux/commit/ba38d03558ac6adcf82eae7fd6759d97ec331256
- Usage examples documented
  - https://github.com/FluidTYPO3/flux/commit/cbf421b69552e5fa44ed2ceebe021f6f20e0cd66
  - https://github.com/FluidTYPO3/flux/commit/5c5866422339d34b9a5e80e947d688bbada41a4b
  - https://github.com/FluidTYPO3/flux/commit/7afcf893fbf82e559cb2d91a2c516f6a6e128ee6
  - https://github.com/FluidTYPO3/flux/commit/be5a69dd0969b020c26395638905966da3685c14
  - https://github.com/FluidTYPO3/flux/commit/7a582a845585b0636a79c1240edc589012493995
  - https://github.com/FluidTYPO3/flux/commit/26e0d037546ff888a24e606cd74a35293377aec1
  - https://github.com/FluidTYPO3/flux/commit/cd53145887d03336f3ee569dc3a499d156dbe5ea
  - https://github.com/FluidTYPO3/flux/commit/9f2d9ca40ca6e30546b88dcfa4ecd816d4a88371
  - https://github.com/FluidTYPO3/flux/commit/4101a89130bee4ec953cb4acae7a7db9d009fc97
- Bugfix to skip language overlays in default language
  - https://github.com/FluidTYPO3/flux/commit/71ce237b627370a36bba67ba3d3da0bc7334ef4a
- Bugfix for `isForeign` check preventing resolving of controller classes by foreign extension key
  - https://github.com/FluidTYPO3/flux/commit/54a0109d72ed2af8ebe280bc640bd7ff53438fb3
- ViewHelpers added for Form options `sorting` and `translation` allowing both to be documented by ViewHelper references
  - https://github.com/FluidTYPO3/flux/commit/aa84fe1a42cd13e57eb65796c39f535ea21d04f4
- CompatibilityRegistry created with the purpose of handling version-dependent configuration, feature flags and class substitution
  - https://github.com/FluidTYPO3/flux/commit/cc3f77f93a3b990b34387557bab32bae380d4f03
  - https://github.com/FluidTYPO3/flux/commit/a7c6cfbf4f557f3655e6fd51aeb814bb96be9aa0
- Bugfix for vendor name not being used when retrieved from controller context (resulting in controller class not being resolved)
  - https://github.com/FluidTYPO3/flux/commit/a00e81b633402735dc681430cdfa6718e4df8dbd
- Every ViewHelper is now compilable, for a significant performance boost in sites using many instances of the same template
  - https://github.com/FluidTYPO3/flux/commit/fdcb40ffd8145f9eb30d25017973b430ada814ae
  - https://github.com/FluidTYPO3/flux/commit/5cf43c76d2659c2138bed321e0c472efcda3bfdc
  - https://github.com/FluidTYPO3/flux/commit/089bab4c591f84056ac1c05f0ef9b1567030c726
  - https://github.com/FluidTYPO3/flux/commit/07e804ced973421e1224a4cd02d693e94070bad9
  - https://github.com/FluidTYPO3/flux/commit/7c32264321387e9edc9786ab5a493777ba3199a2
- Bugfix for localisation behavior when multiple languages exist
  - https://github.com/FluidTYPO3/flux/commit/1bd80f59b92bdcaffc771db3ca0d12863a7e3a80
- TYPO3 7.5 compatibility improvements
  - https://github.com/FluidTYPO3/flux/commit/aef19610862e3b74b184bf8909bb8b715c171b7d
  - https://github.com/FluidTYPO3/flux/commit/43912a3eeb3119bd1387266887b7bc998727a086
  - https://github.com/FluidTYPO3/flux/commit/1fcc911683a32010b8e830b7464db0c0bb652832

7.2.3 - 2015-09-30
------------------

- Bugfix for catching when parent record localisation is deleted
  - https://github.com/FluidTYPO3/flux/commit/8c886e2119a133a482e5f102acfe20293369fd21
- Bugfix to give "show hidden content elements" a default value when rendering child content
  - https://github.com/FluidTYPO3/flux/commit/8cb30d0d9202a98d80c39c1a73d34ebbe108acc6
- Bugfix for relationship of localised child records
  - https://github.com/FluidTYPO3/flux/commit/58602dd9166e2e12fb14e0755d6bccd5455b728a
- Bugfix for incorrect package name in sub request when original request exists
  - https://github.com/FluidTYPO3/flux/commit/dcd6132042e5f8b670f443e6b74398ae8c4db6b6
- Bugfix for path resolution on Windows environments
  - https://github.com/FluidTYPO3/flux/commit/382be33e613dd947805c841a426f16700109e2dc
- TYPO3 7.5 compatibility improvements
  - https://github.com/FluidTYPO3/flux/commit/f2632afd2cd05bc485f6f7c3ec63e6cd449a6f15
  - https://github.com/FluidTYPO3/flux/commit/ebe2989b1587d755eba5aa87cd429ec81421c72a
  - https://github.com/FluidTYPO3/flux/commit/99960c23950348c5f92a2573bce4ea4f5dac8415

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
