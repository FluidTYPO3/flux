<?php
return array(
	'BE' => array(
		'disable_exec_function' => 0,
		'fileCreateMask' => '0664',
		'folderCreateMask' => '2774',
		'forceCharset' => 'utf-8',
		'installToolPassword' => 'bacb98acf97e0b6112b1d1b650b84971',
		'versionNumberInFilename' => '0',
	),
	'DB' => array(
		'database' => 'typo3_test',
		'host' => 'localhost',
		'password' => '',
		'username' => 'root',
	),
	'EXT' => array(
		'extConf' => array(
			'workspaces' => 'a:0:{}',
			'compatibility6' => 'a:0:{}',
		),
	),
	'FE' => array(
		'addRootLineFields' => 'backend_layout',
		'lifetime' => '604800',
		'logfile_dir' => 'localsettings/logs/',
		'pageNotFound_handling' => '/404/',
	),
	'GFX' => array(
		'gdlib_png' => '1',
		'im_noScaleUp' => '1',
		'im_path' => '/usr/bin/',
		'im_version_5' => 'im6',
		'TTFdpi' => '96',
		'thumbnails_png' => '1',
		'jpg_quality' => '80',
	),
	'INSTALL' => array(
		'wizardDone' => array(),
	),
	'SYS' => array(
		'devIPmask' => ',192.168.1.*',
		'displayErrors' => '1',
		'doNotCheckReferer' => '1',
		'enable_DLOG' => 'enable_DLOG',
		'enableDeprecationLog' => 'file',
		'encryptionKey' => 'Travis Tests',
		'forceReturnPath' => '1',
		'setDBinit' => 'SET NAMES utf8',
		'sitename' => 'New TYPO3 site',
		'sqlDebug' => '1',
		'UTF8filesystem' => '1',
		'debugExceptionHandler' => '',
		'setMemoryLimit' => 1024,
	),
);
?>
