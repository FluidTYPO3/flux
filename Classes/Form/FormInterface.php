<?php
namespace FluidTYPO3\Flux\Form;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

/**
 * @package Flux
 * @subpackage Form
 */
interface FormInterface {

	/**
	 * @return array
	 */
	public function build();

	/**
	 * @param string $name
	 */
	public function setName($name);

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @param string $label
	 */
	public function setLabel($label);

	/**
	 * @return string
	 */
	public function getLabel();

	/**
	 * @param string $localLanguageFileRelativePath
	 * @return FormInterface
	 */
	public function setLocalLanguageFileRelativePath($localLanguageFileRelativePath);

	/**
	 * @return string
	 */
	public function getLocalLanguageFileRelativePath();


	/**
	 * @param boolean $disableLocalLanguageLabels
	 * @return FormInterface
	 */
	public function setDisableLocalLanguageLabels($disableLocalLanguageLabels);

	/**
	 * @return boolean
	 */
	public function getDisableLocalLanguageLabels();

	/**
	 * @param ContainerInterface $parent
	 * @return FormInterface
	 */
	public function setParent($parent);

	/**
	 * @return ContainerInterface
	 */
	public function getParent();

	/**
	 * @param array $variables
	 * @return FormInterface
	 */
	public function setVariables($variables);

	/**
	 * @return array
	 */
	public function getVariables();

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return FormInterface
	 */
	public function setVariable($name, $value);

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getVariable($name);

	/**
	 * @return ContainerInterface
	 */
	public function getRoot();

	/**
	 * @return string
	 */
	public function getPath();

	/**
	 * @param string $extensionName
	 * @return FormInterface
	 */
	public function setExtensionName($extensionName);

	/**
	 * @return mixed
	 */
	public function getExtensionName();

	/**
	 * @param string $type
	 * @return boolean
	 */
	public function isChildOfType($type);

	/**
	 * @return boolean
	 */
	public function hasChildren();

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return FieldInterface
	 */
	public function createField($type, $name, $label = NULL);

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return ContainerInterface
	 */
	public function createContainer($type, $name, $label = NULL);

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return WizardInterface
	 */
	public function createWizard($type, $name, $label = NULL);

}
