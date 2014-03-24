<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
 *****************************************************************/

use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\Text;
use FluidTYPO3\Flux\Form\Field\Select;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;

/**
 * Pipe: Flash Message
 *
 * Sends a custom FlashMessage
 *
 * @package Flux
 * @subpackage Outlet\Pipe
 */
class FlashMessagePipe extends AbstractPipe implements PipeInterface {

	/**
	 * @var integer
	 */
	protected $severity = FlashMessage::OK;

	/**
	 * @var boolean
	 */
	protected $storeInSession = TRUE;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @param array $data
	 * @return void
	 */
	public function conduct($data) {
		$flashMessage = new FlashMessage($this->getMessage(), $this->getTitle(), $this->getSeverity(), $this->getStoreInSession());
		FlashMessageQueue::addMessage($flashMessage);
		return $data;
	}

	/**
	 * @return FieldInterface[]
	 */
	public function getFormFields() {
		$severities = array(
			FlashMessage::OK => 'OK',
			FlashMessage::ERROR => 'ERROR',
			FlashMessage::NOTICE => 'NOTICE',
			FlashMessage::WARNING => 'WARNING'
		);
		$fields = parent::getFormFields();
		$fields['message'] = Text::create(array('type' => 'Text'))
			->setName('message');
		$fields['title'] = Input::create(array('type' => 'Input'))
			->setName('title');
		$fields['severity'] = Select::create(array('type' => 'Select'))
			->setName('severity')
			->setItems($severities)
			->setDefault(FlashMessage::OK);
		return $fields;
	}

	/**
	 * @param integer $severity
	 * @return FlashMessagePipe
	 */
	public function setSeverity($severity) {
		$this->severity = $severity;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getSeverity() {
		return $this->severity;
	}

	/**
	 * @param boolean $storeInSession
	 * @return FlashMessagePipe
	 */
	public function setStoreInSession($storeInSession) {
		$this->storeInSession = $storeInSession;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getStoreInSession() {
		return $this->storeInSession;
	}

	/**
	 * @param string $title
	 * @return FlashMessagePipe
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $message
	 * @return FlashMessagePipe
	 */
	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

}
