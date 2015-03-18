<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Add;

/**
 * @package Flux
 * @subpackage Form
 */
abstract class AbstractWizard extends AbstractFormComponent implements WizardInterface {

	/**
	 * @var boolean
	 */
	protected $hideParent = FALSE;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $icon;

	/**
	 * @var string
	 */
	protected $script;

	/**
	 * @return array
	 */
	public function build() {
		$structure = array(
			'type' => $this->type,
			'title' => $this->getLabel(),
			'icon' => $this->icon,
			'script' => $this->script,
			'hideParent' => intval($this->getHideParent()),
		);
		$configuration = $this->buildConfiguration();
		$structure = array_merge($structure, $configuration);
		return $structure;
	}

	/**
	 * @param boolean $hideParent
	 * @return Add
	 */
	public function setHideParent($hideParent) {
		$this->hideParent = $hideParent;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getHideParent() {
		return $this->hideParent;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		return FALSE;
	}
}
