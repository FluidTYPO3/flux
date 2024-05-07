<?php
$EM_CONF['flux'] = array (
  'title' => 'Flux: Fluid Integration',
  'description' => 'Drop-in page and content templates with nested content feature. Provides multiple condensed integration APIs to use advanced TYPO3 features with little effort.',
  'category' => 'misc',
  'shy' => 0,
  'version' => '10.0.10',
  'dependencies' => 'cms',
  'conflicts' => '',
  'priority' => 'top',
  'loadOrder' => '',
  'module' => '',
  'state' => 'beta',
  'uploadfolder' => 0,
  'createDirs' => '',
  'modify_tables' => '',
  'clearcacheonload' => 1,
  'lockType' => '',
  'author' => 'FluidTYPO3 Team',
  'author_email' => 'claus@namelesscoder.net',
  'author_company' => '',
  'CGLcompliance' => '',
  'CGLcompliance_note' => '',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '10.4.0-12.4.99',
      'php' => '7.4.0-8.2.99',
    ),
    'conflicts' => 
    array (
      'fluidpages' => '',
      'fluidcontent' => '',
    ),
    'suggests' => 
    array (
    ),
  ),
  '_md5_values_when_last_written' => '',
  'suggests' => 
  array (
  ),
  'autoload' => 
  array (
    'psr-4' => 
    array (
      'FluidTYPO3\\Flux\\' => 'Classes/',
    ),
  ),
  'autoload-dev' => 
  array (
    'psr-4' => 
    array (
      'FluidTYPO3\\Flux\\Tests\\' => 'Tests/',
    ),
  ),
);
