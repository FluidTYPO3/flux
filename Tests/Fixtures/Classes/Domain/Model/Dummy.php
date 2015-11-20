<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes\Domain\Model;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @Flux\Control\Hide
 * @Flux\Control\Delete
 * @Flux\Control\StartTime
 * @Flux\Control\EndTime
 * @Flux\Control\FrontendUserGroup
 */
class Dummy extends AbstractEntity {

	/**
	 * @var \DateTime
	 * @Flux\Form\Field dateTime
	 * @Flux\Form\Sheet options
	 */
	protected $crdate;

	/**
	 * @var string
	 * @Flux\Label
	 * @Flux\Form\Sheet options
	 * @Flux\Form\Field input(size: 40)
	 */
	protected $title;

	/**
	 * @param \DateTime $crdate
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
