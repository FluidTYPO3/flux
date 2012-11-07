<?php

########################################################################
# Extension Manager/Repository config file for ext "flux".
#
# Auto generated 07-11-2012 22:33
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Dynamic Fluid FlexForms',
	'description' => 'Uses Fluid to render FlexForms, making them highly dynamic. Has built-in content preview in BE page module for any content using Flux. Offspring of the FED extension.',
	'category' => 'misc',
	'shy' => 0,
	'version' => '4.7.9',
	'dependencies' => 'cms,extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Claus Due',
	'author_email' => 'claus@wildside.dk',
	'author_company' => 'Wildside A/S',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'extbase' => '',
			'fluid' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'gridelements' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:119:{s:16:"ext_autoload.php";s:4:"cfda";s:21:"ext_conf_template.txt";s:4:"6623";s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"38f2";s:14:"ext_tables.php";s:4:"e161";s:14:"ext_tables.sql";s:4:"2842";s:9:"README.md";s:4:"31db";s:16:"Classes/Core.php";s:4:"3c6d";s:42:"Classes/Backend/AreaListItemsProcessor.php";s:4:"1059";s:35:"Classes/Backend/DynamicFlexForm.php";s:4:"57cd";s:60:"Classes/Backend/ExtendedColumnPositionListItemsProcessor.php";s:4:"7f4b";s:27:"Classes/Backend/Preview.php";s:4:"0080";s:62:"Classes/Backend/StandaloneColumnPositionListItemsProcessor.php";s:4:"80df";s:27:"Classes/Backend/TceMain.php";s:4:"2a08";s:38:"Classes/Backend/TemplaVoilaPreview.php";s:4:"e547";s:37:"Classes/Controller/FluxController.php";s:4:"7d70";s:53:"Classes/Core/ViewHelper/AbstractBackendViewHelper.php";s:4:"e24e";s:54:"Classes/Core/ViewHelper/AbstractFlexformViewHelper.php";s:4:"4987";s:42:"Classes/MVC/View/ExposedStandaloneView.php";s:4:"b8ff";s:40:"Classes/MVC/View/ExposedTemplateView.php";s:4:"2bcd";s:50:"Classes/Provider/AbstractConfigurationProvider.php";s:4:"a6b3";s:63:"Classes/Provider/AbstractContentObjectConfigurationProvider.php";s:4:"bfd5";s:56:"Classes/Provider/AbstractPluginConfigurationProvider.php";s:4:"ddf4";s:51:"Classes/Provider/ConfigurationProviderInterface.php";s:4:"416f";s:41:"Classes/Provider/ConfigurationService.php";s:4:"3af2";s:64:"Classes/Provider/ContentObjectConfigurationProviderInterface.php";s:4:"3458";s:57:"Classes/Provider/PluginConfigurationProviderInterface.php";s:4:"5e0d";s:47:"Classes/Provider/StructureProviderInterface.php";s:4:"1c15";s:69:"Classes/Provider/Configuration/ContentObjectConfigurationProvider.php";s:4:"6e62";s:65:"Classes/Provider/Configuration/Fallback/ConfigurationProvider.php";s:4:"ed39";s:78:"Classes/Provider/Configuration/Fallback/ContentObjectConfigurationProvider.php";s:4:"941d";s:71:"Classes/Provider/Configuration/Fallback/PluginConfigurationProvider.php";s:4:"d089";s:56:"Classes/Provider/Structure/AbstractStructureProvider.php";s:4:"f8c5";s:56:"Classes/Provider/Structure/FallbackStructureProvider.php";s:4:"9c16";s:56:"Classes/Provider/Structure/FlexFormStructureProvider.php";s:4:"4d20";s:53:"Classes/Provider/Structure/SheetStructureProvider.php";s:4:"f361";s:62:"Classes/Provider/Structure/Field/CheckboxStructureProvider.php";s:4:"dffe";s:60:"Classes/Provider/Structure/Field/CustomStructureProvider.php";s:4:"cf82";s:59:"Classes/Provider/Structure/Field/GroupStructureProvider.php";s:4:"c1d0";s:58:"Classes/Provider/Structure/Field/HtmlStructureProvider.php";s:4:"2fc4";s:59:"Classes/Provider/Structure/Field/InputStructureProvider.php";s:4:"e2ea";s:65:"Classes/Provider/Structure/Field/PassthroughStructureProvider.php";s:4:"2377";s:61:"Classes/Provider/Structure/Field/SectionStructureProvider.php";s:4:"4341";s:60:"Classes/Provider/Structure/Field/SelectStructureProvider.php";s:4:"008a";s:58:"Classes/Provider/Structure/Field/TextStructureProvider.php";s:4:"f5df";s:62:"Classes/Provider/Structure/Field/UserFuncStructureProvider.php";s:4:"5148";s:27:"Classes/Service/Content.php";s:4:"0c36";s:28:"Classes/Service/FlexForm.php";s:4:"41f0";s:50:"Classes/Service/FluidFlexFormTemplateValidator.php";s:4:"1636";s:24:"Classes/Service/Grid.php";s:4:"d1f3";s:24:"Classes/Service/Json.php";s:4:"760d";s:38:"Classes/UserFunction/ErrorReporter.php";s:4:"1511";s:35:"Classes/UserFunction/HtmlOutput.php";s:4:"1b7e";s:33:"Classes/UserFunction/NoFields.php";s:4:"573a";s:35:"Classes/UserFunction/NoTemplate.php";s:4:"ab8c";s:25:"Classes/Utility/Array.php";s:4:"d413";s:28:"Classes/Utility/Autoload.php";s:4:"4201";s:24:"Classes/Utility/Path.php";s:4:"f1bf";s:42:"Classes/ViewHelpers/FlexformViewHelper.php";s:4:"ec48";s:43:"Classes/ViewHelpers/LowercaseViewHelper.php";s:4:"5329";s:42:"Classes/ViewHelpers/VariableViewHelper.php";s:4:"52bb";s:48:"Classes/ViewHelpers/Be/ContentAreaViewHelper.php";s:4:"7053";s:51:"Classes/ViewHelpers/Be/ContentElementViewHelper.php";s:4:"2850";s:53:"Classes/ViewHelpers/Be/Link/Content/NewViewHelper.php";s:4:"e82f";s:50:"Classes/ViewHelpers/Flexform/ContentViewHelper.php";s:4:"a7bb";s:47:"Classes/ViewHelpers/Flexform/GridViewHelper.php";s:4:"d16c";s:49:"Classes/ViewHelpers/Flexform/ObjectViewHelper.php";s:4:"b998";s:56:"Classes/ViewHelpers/Flexform/RenderContentViewHelper.php";s:4:"59ce";s:50:"Classes/ViewHelpers/Flexform/SectionViewHelper.php";s:4:"dc5e";s:48:"Classes/ViewHelpers/Flexform/SheetViewHelper.php";s:4:"8ea9";s:62:"Classes/ViewHelpers/Flexform/Field/AbstractFieldViewHelper.php";s:4:"8984";s:57:"Classes/ViewHelpers/Flexform/Field/CheckboxViewHelper.php";s:4:"9c7b";s:66:"Classes/ViewHelpers/Flexform/Field/ControllerActionsViewHelper.php";s:4:"a238";s:55:"Classes/ViewHelpers/Flexform/Field/CustomViewHelper.php";s:4:"60a5";s:53:"Classes/ViewHelpers/Flexform/Field/FileViewHelper.php";s:4:"f30e";s:54:"Classes/ViewHelpers/Flexform/Field/GroupViewHelper.php";s:4:"a8c0";s:53:"Classes/ViewHelpers/Flexform/Field/HtmlViewHelper.php";s:4:"a3ab";s:54:"Classes/ViewHelpers/Flexform/Field/InputViewHelper.php";s:4:"5f43";s:55:"Classes/ViewHelpers/Flexform/Field/SelectViewHelper.php";s:4:"0613";s:53:"Classes/ViewHelpers/Flexform/Field/TextViewHelper.php";s:4:"d1d6";s:53:"Classes/ViewHelpers/Flexform/Field/TreeViewHelper.php";s:4:"ccf3";s:57:"Classes/ViewHelpers/Flexform/Field/UserFuncViewHelper.php";s:4:"c16c";s:56:"Classes/ViewHelpers/Flexform/Field/WizardsViewHelper.php";s:4:"2e02";s:70:"Classes/ViewHelpers/Flexform/Field/Wizard/AbstractWizardViewHelper.php";s:4:"6a8c";s:59:"Classes/ViewHelpers/Flexform/Field/Wizard/AddViewHelper.php";s:4:"ee44";s:67:"Classes/ViewHelpers/Flexform/Field/Wizard/ColorPickerViewHelper.php";s:4:"244c";s:60:"Classes/ViewHelpers/Flexform/Field/Wizard/EditViewHelper.php";s:4:"9dd6";s:60:"Classes/ViewHelpers/Flexform/Field/Wizard/LinkViewHelper.php";s:4:"9a73";s:60:"Classes/ViewHelpers/Flexform/Field/Wizard/ListViewHelper.php";s:4:"a208";s:62:"Classes/ViewHelpers/Flexform/Field/Wizard/SelectViewHelper.php";s:4:"4e1c";s:62:"Classes/ViewHelpers/Flexform/Field/Wizard/SliderViewHelper.php";s:4:"523e";s:63:"Classes/ViewHelpers/Flexform/Field/Wizard/SuggestViewHelper.php";s:4:"38d8";s:71:"Classes/ViewHelpers/Flexform/Field/Wizard/WizardViewHelperInterface.php";s:4:"9657";s:54:"Classes/ViewHelpers/Flexform/Grid/ColumnViewHelper.php";s:4:"c8f3";s:51:"Classes/ViewHelpers/Flexform/Grid/RowViewHelper.php";s:4:"c53c";s:64:"Classes/ViewHelpers/Flexform/Object/AbstractObjectViewHelper.php";s:4:"ea0c";s:57:"Classes/ViewHelpers/Flexform/Object/ContentViewHelper.php";s:4:"0b09";s:54:"Classes/ViewHelpers/Flexform/Object/FileViewHelper.php";s:4:"2040";s:55:"Classes/ViewHelpers/Flexform/Object/ImageViewHelper.php";s:4:"661b";s:54:"Classes/ViewHelpers/Flexform/Object/LinkViewHelper.php";s:4:"8f3d";s:55:"Classes/ViewHelpers/Flexform/Object/PagesViewHelper.php";s:4:"d74a";s:55:"Classes/ViewHelpers/Flexform/Object/VideoViewHelper.php";s:4:"cd29";s:75:"Classes/ViewHelpers/Flexform/Object/Controller/StandardObjectController.php";s:4:"e853";s:45:"Classes/ViewHelpers/Widget/GridViewHelper.php";s:4:"e280";s:56:"Classes/ViewHelpers/Widget/Controller/GridController.php";s:4:"5dec";s:42:"Classes/ViewHelpers/Xml/NodeViewHelper.php";s:4:"35ad";s:40:"Resources/Private/Language/locallang.xml";s:4:"2eb2";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"9f15";s:54:"Resources/Private/Partials/Flexform/Object/Content.xml";s:4:"5a96";s:51:"Resources/Private/Partials/Flexform/Object/File.xml";s:4:"65e2";s:52:"Resources/Private/Partials/Flexform/Object/Image.xml";s:4:"8c6a";s:52:"Resources/Private/Partials/Flexform/Object/Pages.xml";s:4:"0a56";s:52:"Resources/Private/Partials/Flexform/Object/Video.xml";s:4:"1323";s:62:"Resources/Private/Templates/ViewHelpers/Widget/Grid/Index.html";s:4:"d85e";s:37:"Resources/Public/Icons/ColorWheel.png";s:4:"0647";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:31:"Scripts/CommandLineLauncher.php";s:4:"3cd6";s:28:"Scripts/DynFlexMigration.php";s:4:"cf00";s:14:"doc/manual.sxw";s:4:"7490";}',
	'suggests' => array(
	),
);

?>