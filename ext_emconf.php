<?php
$EM_CONF[$_EXTKEY] = array (
  'title' => 'Flux: Fluid FlexForms',
  'description' => 'Backend form and frontend content rendering assistance API with focus on productivity.',
  'category' => 'misc',
  'shy' => 0,
  'version' => '8.2.1',
  'dependencies' => 'cms',
  'conflicts' => '',
  'priority' => 'top',
  'loadOrder' => '',
  'module' => '',
  'state' => 'stable',
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
      'typo3' => '7.6.13-8.7.99',
      'php' => '7.0.0-7.1.99',
    ),
    'conflicts' => 
    array (
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
