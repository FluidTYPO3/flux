<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;

/**
 * ContentController
 */
class ContentController extends AbstractFluxController {

	/**
	 * @return void
	 */
	public function initializeProvider() {

	}

	/**
	 * @return void
	 */
	public function initializeOverriddenSettings() {
		$this->settings = RecursiveArrayUtility::merge($this->settings, $this->data['settings']);
	}

	/**
	 * Fake Action
	 *
	 * @return void
	 */
	public function fakeAction() {
	}

	/**
	 * @return void
	 */
	public function fakeWithoutDescriptionAction() {
	}

	/**
	 * Fake Action
	 *
	 * @param string $required
	 * @return void
	 */
	public function fakeWithRequiredArgumentAction($required) {
	}

}
