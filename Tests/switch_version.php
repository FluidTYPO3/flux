<?php

$version = $argv[1];

if (empty($version)) {
    echo 'Version must be specified as first argument';
    exit(1);
}

$composerArguments = [
    '10.4' => '--ignore-platform-reqs',
];

$currentLockFile = null;
if (file_exists('composer.lock')) {
    $currentLockFile = 'composer.lock';
}

$currentVendorDir = null;
if (file_exists('vendor')) {
    $currentVendorDir = 'vendor';
}

$preflightDirectory = 'preflight/';
$directory = $preflightDirectory . $version . '/';

// Check if a preserved "vendor" and "composer.lock" exists for the version. If not, create them.
if (!file_exists($directory)) {
    echo 'Creating: ' . $directory . PHP_EOL;
    mkdir($directory);
}

echo 'Current lockfile: . ' . $currentLockFile . PHP_EOL;
echo 'Current vendor dir: . ' . $currentVendorDir . PHP_EOL;

$versionSpecificVendorDirectory = $directory . 'vendor';
if (!file_exists($versionSpecificVendorDirectory)) {
    if ($currentLockFile && file_exists($currentLockFile)) {
        system('rm -rf ' . $currentLockFile);
    }
    if ($currentVendorDir && file_exists($currentVendorDir)) {
        system('rm -rf ' . $currentVendorDir);
    }
    system('composer req typo3/cms-core:^' . $version . (isset($composerArguments[$version]) ? ' ' . $composerArguments[$version] : ''));
    copy('composer.lock', $directory . 'composer.lock');
    system('git checkout composer.json');
    system('mv vendor ' . $directory);
}

if ($currentLockFile && file_exists($currentLockFile)) {
    system('rm -rf ' . $currentLockFile);
}

if ($currentLockFile && file_exists($currentLockFile)) {
    system('rm -rf ' . $currentVendorDir);
}

system('cp -R ' . $directory . 'composer.lock ./composer.lock');
system('cp -R ' . $directory . 'vendor ./vendor');
