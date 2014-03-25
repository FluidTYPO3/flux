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
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * Email Pipe
 *
 * Sends an email with a dump of the data in its current state.
 * Chain with other Pipes to convert data before it reaches this
 * Pipe if you want to - just as an example - create a proper
 * email body text containing a nice representaton of the data.
 *
 * @package Flux
 * @subpackage Outlet\Pipe
 */
class EmailPipe extends AbstractPipe implements PipeInterface {

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var mixed
	 */
	protected $recipient;

	/**
	 * @var mixed
	 */
	protected $sender;

	/**
	 * @var string|NULL
	 */
	protected $body = NULL;

	/**
	 * @return FieldInterface[]
	 */
	public function getFormFields() {
		$fields = parent::getFormFields();
		$fields['subject'] = Input::create(array('type' => 'Input'))
			->setName('subject');
		$fields['body'] = Text::create(array('type' => 'Text'))
			->setName('body');
		$fields['receipent'] = Input::create(array('type' => 'Input'))
			->setName('recipient');
		$fields['sender'] = Input::create(array('type' => 'Input'))
			->setName('sender');
		return $fields;
	}

	/**
	 * @param string $recipient
	 * @return EmailPipe
	 */
	public function setRecipient($recipient) {
		$this->recipient = $recipient;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRecipient() {
		return $this->recipient;
	}

	/**
	 * @param string $sender
	 * @return EmailPipe
	 */
	public function setSender($sender) {
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * @param string $subject
	 * @return EmailPipe
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @param string|NULL $body
	 * @return EmailPipe
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param mixed $data
	 * @return mixed
	 * @throws Exception
	 */
	public function conduct($data) {
		try {
			$message = $this->prepareEmail($data);
			$this->sendEmail($message);
		} catch (\Swift_RfcComplianceException $error) {
			throw new Exception($error->getMessage(), $error->getCode());
		}
		return $data;
	}

	/**
	 * @param string $data
	 * @return MailMessage
	 */
	protected function prepareEmail($data) {
		$body = $this->getBody();
		$sender = $this->getSender();
		$recipient = $this->getRecipient();
		if (TRUE === is_array($recipient)) {
			list ($recipientAddress, $recipientName) = $recipient;
		} else {
			$recipientAddress = $recipient;
			$recipientName = NULL;
		}
		if (TRUE === is_array($sender)) {
			list ($senderAddress, $senderName) = $sender;
		} else {
			$senderAddress = $sender;
			$senderName = NULL;
		}
		$subject = $this->getSubject();
		if (TRUE === is_string($data)) {
			$body = $data;
		}
		$message = new MailMessage();
		$message->setSubject($subject);
		$message->setFrom($senderAddress, $senderName);
		$message->setTo($recipientAddress, $recipientName);
		$message->setBody($body);
		return $message;
	}

	/**
	 * @param MailMessage $message
	 * @return void
	 */
	protected function sendEmail(MailMessage $message) {
		$message->send();
	}

}
