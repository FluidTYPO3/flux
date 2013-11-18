<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * ************************************************************* */

/**
 * @Flux\Control\Hide
 * @Flux\Control\Delete
 * @Flux\Control\StartTime
 * @Flux\Control\EndTime
 * @Flux\Control\FrontendUserGroup
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Domain_Model_Dummy extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @Flux\Form\Field dateTime
	 * @Flux\Form\Sheet options
	 * @var DateTime
	 */
	protected $crdate;

	/**
	 * @Flux\Label
	 * @Flux\Form\Sheet options
	 * @Flux\Form\Field input(size: 40)
	 * @var string
	 */
	protected $title;

	/**
	 * @param DateTime $crdate
	 * @return void
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * @return \DateTime
	 */
	public function getCrdate() {
		return $this->crdate;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return NULL
	 */
	public function getThisMethodIsAnIgnoredGetter() {
		return NULL;
	}

}
