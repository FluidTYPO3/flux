<?php
// Register composer autoloader
$autoloaderFolders = [
    trim(shell_exec('pwd')) . '/vendor/',
    __DIR__ . '/../vendor/'
];
foreach ($autoloaderFolders as $autoloaderFolder) {
    if (file_exists($autoloaderFolder . 'autoload.php')) {
        /** @var Composer\Autoload\ClassLoader $autoloader */
        $autoloader = require $autoloaderFolder . 'autoload.php';
        if (!getenv('TYPO3_PATH_ROOT')) {
            $path = realpath($autoloaderFolder . '../') . '/';
            $pwd = trim(shell_exec('pwd'));
            if (file_exists($pwd . '/composer.json')) {
                $json = json_decode(file_get_contents($pwd . '/composer.json'), true);
                if ($json['extra']['typo3/cms']['web-dir'] ?? false) {
                    $path .= $json['extra']['typo3/cms']['web-dir'] . '/';
                }
            }
            putenv('TYPO3_PATH_ROOT=' . $path);
        }
        break;
    }
}

if (!isset($autoloader)) {
	throw new \RuntimeException(
		'Could not find autoload.php, make sure you ran composer.'
	);
}

$autoloader->addPsr4('FluidTYPO3\\Flux\\Tests\\', getenv('TYPO3_PATH_ROOT') . 'typo3conf/ext/flux/Tests/');

\FluidTYPO3\Development\Bootstrap::initialize(
	$autoloader,
	array(
		'cache_pages' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'cache_hash' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'l10n' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'fluid_template' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
		'cache_core' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
		'cache_runtime' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_reflection' => \FluidTYPO3\Development\Bootstrap::CACHE_MEMORY,
		'extbase_object' => \FluidTYPO3\Development\Bootstrap::CACHE_MEMORY,
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
