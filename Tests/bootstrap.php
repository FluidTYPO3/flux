<?php
// Register composer autoloader
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
	throw new \RuntimeException(
		'Could not find vendor/autoload.php, make sure you ran composer.'
	);
}

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require __DIR__ . '/../vendor/autoload.php';

\FluidTYPO3\Development\Bootstrap::initialize(
	$autoloader,
	array(
		'cache_pages' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'cache_hash' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_reflection' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'l10n' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'fluid_template' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
		'cache_core' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
		'cache_runtime' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_object' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_datamapfactory_datamap' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_typo3dbbackend_tablecolumns' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_typo3dbbackend_queries' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'fluidcontent' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'flux' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'assets' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	),
	array(
		'core'
	)
);

class_alias('FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\ContentController', 'FluidTYPO3\\Flux\\Controller\\ContentController');
class_alias('FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\ContentController', 'Tx_Flux_Controller_ContentController');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] = [
    'f' => [
        'TYPO3Fluid\Fluid\ViewHelpers',
        'TYPO3\CMS\Fluid\ViewHelpers'
    ]
];
