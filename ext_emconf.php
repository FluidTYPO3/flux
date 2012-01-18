<?php

########################################################################
# Extension Manager/Repository config file for ext "flux".
#
# Auto generated 17-01-2012 21:03
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
	'version' => '1.2.6',
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
	'_md5_values_when_last_written' => 'a:43:{s:21:"ExtensionBuilder.json";s:4:"e721";s:16:"ext_autoload.php";s:4:"7589";s:21:"ext_conf_template.txt";s:4:"2f85";s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"dcea";s:14:"ext_tables.php";s:4:"2436";s:14:"ext_tables.sql";s:4:"d41d";s:16:"Classes/Core.php";s:4:"ff6d";s:35:"Classes/Backend/DynamicFlexForm.php";s:4:"3245";s:27:"Classes/Backend/Preview.php";s:4:"c7c3";s:46:"Classes/Configuration/ConfigurationManager.php";s:4:"824b";s:37:"Classes/Controller/FluxController.php";s:4:"d3ac";s:54:"Classes/Core/ViewHelper/AbstractFlexformViewHelper.php";s:4:"39df";s:42:"Classes/MVC/View/ExposedStandaloneView.php";s:4:"6a4b";s:40:"Classes/MVC/View/ExposedTemplateView.php";s:4:"13a6";s:28:"Classes/Service/FlexForm.php";s:4:"430f";s:24:"Classes/Service/Grid.php";s:4:"f608";s:24:"Classes/Service/Json.php";s:4:"efd6";s:38:"Classes/UserFunction/ErrorReporter.php";s:4:"36a0";s:33:"Classes/UserFunction/NoFields.php";s:4:"d66a";s:42:"Classes/ViewHelpers/FlexformViewHelper.php";s:4:"655c";s:47:"Classes/ViewHelpers/Flexform/GridViewHelper.php";s:4:"8194";s:54:"Classes/ViewHelpers/Flexform/RenderFieldViewHelper.php";s:4:"ed12";s:48:"Classes/ViewHelpers/Flexform/SheetViewHelper.php";s:4:"bca7";s:62:"Classes/ViewHelpers/Flexform/Field/AbstractFieldViewHelper.php";s:4:"03a4";s:57:"Classes/ViewHelpers/Flexform/Field/CheckboxViewHelper.php";s:4:"8e5a";s:54:"Classes/ViewHelpers/Flexform/Field/GroupViewHelper.php";s:4:"9ccc";s:54:"Classes/ViewHelpers/Flexform/Field/InputViewHelper.php";s:4:"95a9";s:55:"Classes/ViewHelpers/Flexform/Field/SelectViewHelper.php";s:4:"ac86";s:53:"Classes/ViewHelpers/Flexform/Field/TextViewHelper.php";s:4:"f58d";s:53:"Classes/ViewHelpers/Flexform/Field/TreeViewHelper.php";s:4:"bb92";s:57:"Classes/ViewHelpers/Flexform/Field/UserFuncViewHelper.php";s:4:"614d";s:54:"Classes/ViewHelpers/Flexform/Grid/ColumnViewHelper.php";s:4:"d106";s:51:"Classes/ViewHelpers/Flexform/Grid/RowViewHelper.php";s:4:"de7d";s:45:"Classes/ViewHelpers/Widget/GridViewHelper.php";s:4:"5fa6";s:56:"Classes/ViewHelpers/Widget/Controller/GridController.php";s:4:"ed01";s:44:"Configuration/ExtensionBuilder/settings.yaml";s:4:"faa4";s:40:"Resources/Private/Language/locallang.xml";s:4:"c0f7";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"c115";s:43:"Resources/Private/Partials/AutoFlexForm.xml";s:4:"1d36";s:62:"Resources/Private/Templates/ViewHelpers/Widget/Grid/Index.html";s:4:"a591";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:14:"doc/manual.sxw";s:4:"7490";}',
	'suggests' => array(
	),
);

?>