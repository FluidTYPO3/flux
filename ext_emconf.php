<?php

########################################################################
# Extension Manager/Repository config file for ext "flux".
#
# Auto generated 29-02-2012 18:18
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
	'version' => '1.2.9',
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
		),
	),
	'_md5_values_when_last_written' => 'a:75:{s:21:"ExtensionBuilder.json";s:4:"e721";s:16:"ext_autoload.php";s:4:"3656";s:21:"ext_conf_template.txt";s:4:"2f85";s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"0e40";s:14:"ext_tables.php";s:4:"fb2b";s:14:"ext_tables.sql";s:4:"e415";s:16:"Classes/Core.php";s:4:"ed70";s:35:"Classes/Backend/DynamicFlexForm.php";s:4:"c630";s:34:"Classes/Backend/MakeQueryArray.php";s:4:"bef8";s:27:"Classes/Backend/Preview.php";s:4:"9f65";s:27:"Classes/Backend/TceMain.php";s:4:"4470";s:38:"Classes/Backend/TemplaVoilaPreview.php";s:4:"e62f";s:46:"Classes/Configuration/ConfigurationManager.php";s:4:"824b";s:37:"Classes/Controller/FluxController.php";s:4:"d3ac";s:53:"Classes/Core/ViewHelper/AbstractBackendViewHelper.php";s:4:"9776";s:54:"Classes/Core/ViewHelper/AbstractFlexformViewHelper.php";s:4:"3fc5";s:42:"Classes/MVC/View/ExposedStandaloneView.php";s:4:"6a4b";s:40:"Classes/MVC/View/ExposedTemplateView.php";s:4:"13a6";s:50:"Classes/Provider/AbstractConfigurationProvider.php";s:4:"a910";s:63:"Classes/Provider/AbstractContentObjectConfigurationProvider.php";s:4:"5949";s:56:"Classes/Provider/AbstractPluginConfigurationProvider.php";s:4:"d0db";s:51:"Classes/Provider/ConfigurationProviderInterface.php";s:4:"1007";s:41:"Classes/Provider/ConfigurationService.php";s:4:"5ac9";s:64:"Classes/Provider/ContentObjectConfigurationProviderInterface.php";s:4:"483b";s:57:"Classes/Provider/PluginConfigurationProviderInterface.php";s:4:"32c0";s:65:"Classes/Provider/Configuration/Fallback/ConfigurationProvider.php";s:4:"a75a";s:78:"Classes/Provider/Configuration/Fallback/ContentObjectConfigurationProvider.php";s:4:"3e7e";s:71:"Classes/Provider/Configuration/Fallback/PluginConfigurationProvider.php";s:4:"d24f";s:27:"Classes/Service/Content.php";s:4:"fe07";s:28:"Classes/Service/FlexForm.php";s:4:"209b";s:24:"Classes/Service/Grid.php";s:4:"f608";s:24:"Classes/Service/Json.php";s:4:"efd6";s:38:"Classes/UserFunction/ErrorReporter.php";s:4:"1ee1";s:33:"Classes/UserFunction/NoFields.php";s:4:"d66a";s:42:"Classes/ViewHelpers/FlexformViewHelper.php";s:4:"dcaf";s:43:"Classes/ViewHelpers/LowercaseViewHelper.php";s:4:"222f";s:48:"Classes/ViewHelpers/Be/ContentAreaViewHelper.php";s:4:"5a6a";s:51:"Classes/ViewHelpers/Be/ContentElementViewHelper.php";s:4:"ea34";s:53:"Classes/ViewHelpers/Be/Link/Content/NewViewHelper.php";s:4:"8885";s:50:"Classes/ViewHelpers/Flexform/ContentViewHelper.php";s:4:"cb36";s:47:"Classes/ViewHelpers/Flexform/GridViewHelper.php";s:4:"8194";s:56:"Classes/ViewHelpers/Flexform/RenderContentViewHelper.php";s:4:"a5c6";s:48:"Classes/ViewHelpers/Flexform/SheetViewHelper.php";s:4:"bca7";s:62:"Classes/ViewHelpers/Flexform/Field/AbstractFieldViewHelper.php";s:4:"03a4";s:57:"Classes/ViewHelpers/Flexform/Field/CheckboxViewHelper.php";s:4:"06d0";s:55:"Classes/ViewHelpers/Flexform/Field/CustomViewHelper.php";s:4:"ffc8";s:54:"Classes/ViewHelpers/Flexform/Field/GroupViewHelper.php";s:4:"d67a";s:54:"Classes/ViewHelpers/Flexform/Field/InputViewHelper.php";s:4:"0f08";s:55:"Classes/ViewHelpers/Flexform/Field/SelectViewHelper.php";s:4:"d2d9";s:53:"Classes/ViewHelpers/Flexform/Field/TextViewHelper.php";s:4:"7133";s:53:"Classes/ViewHelpers/Flexform/Field/TreeViewHelper.php";s:4:"11f9";s:57:"Classes/ViewHelpers/Flexform/Field/UserFuncViewHelper.php";s:4:"fee6";s:54:"Classes/ViewHelpers/Flexform/Grid/ColumnViewHelper.php";s:4:"d106";s:51:"Classes/ViewHelpers/Flexform/Grid/RowViewHelper.php";s:4:"de7d";s:45:"Classes/ViewHelpers/Widget/GridViewHelper.php";s:4:"209e";s:56:"Classes/ViewHelpers/Widget/Controller/GridController.php";s:4:"ed01";s:42:"Classes/ViewHelpers/Xml/NodeViewHelper.php";s:4:"a70d";s:44:"Configuration/ExtensionBuilder/settings.yaml";s:4:"faa4";s:40:"Resources/Private/Language/locallang.xml";s:4:"c0f7";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"c115";s:43:"Resources/Private/Partials/AutoFlexForm.xml";s:4:"d39b";s:36:"Resources/Private/Partials/Sheet.xml";s:4:"8f67";s:46:"Resources/Private/Partials/Fields/Checkbox.xml";s:4:"16a9";s:44:"Resources/Private/Partials/Fields/Custom.xml";s:4:"2d34";s:43:"Resources/Private/Partials/Fields/Group.xml";s:4:"704e";s:43:"Resources/Private/Partials/Fields/Input.xml";s:4:"6dd2";s:44:"Resources/Private/Partials/Fields/Select.xml";s:4:"7a64";s:42:"Resources/Private/Partials/Fields/Text.xml";s:4:"aa33";s:42:"Resources/Private/Partials/Fields/User.xml";s:4:"0dda";s:62:"Resources/Private/Templates/ViewHelpers/Widget/Grid/Index.html";s:4:"4d2f";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:31:"Scripts/CommandLineLauncher.php";s:4:"5627";s:28:"Scripts/DynFlexMigration.php";s:4:"193a";s:14:"doc/manual.sxw";s:4:"7490";}',
	'suggests' => array(
	),
);

?>