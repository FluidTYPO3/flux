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
 * ************************************************************* */

use FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe\AbstractPipeTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class EmailPipeTest extends AbstractPipeTestCase {

	/**
	 * @var array
	 */
	protected $defaultData = array('subject' => 'Test subject');

	/**
	 * @return EmailPipe
	 */
	protected function createInstance() {
		$pipe = $this->getMock('FluidTYPO3\Flux\Outlet\Pipe\EmailPipe', array('sendEmail'));
		ObjectAccess::setProperty($pipe, 'label', 'Mock EmailPipe', TRUE);
		return $pipe;
	}

	/**
	 * @test
	 */
	public function supportsSenderArray() {
		$instance = $this->createInstance();
		$instance->setSender(array('test@test.com', 'test'));
		$output = $instance->conduct($this->defaultData);
		$this->assertNotEmpty($output);
	}

	/**
	 * @test
	 */
	public function supportsRecipientArray() {
		$instance = $this->createInstance();
		$instance->setRecipient(array('test@test.com', 'test'));
		$output = $instance->conduct($this->defaultData);
		$this->assertNotEmpty($output);
	}

	/**
	 * @test
	 */
	public function supportsStringData() {
		$instance = $this->createInstance();
		$output = $instance->conduct('test');
		$this->assertNotEmpty($output);
	}

	/**
	 * @test
	 */
	public function turnsMailboxValidationErrorIntoPipeException() {
		$instance = $this->createInstance();
		$this->setExpectedException('FluidTYPO3\Flux\Outlet\Pipe\Exception');
		$instance->setRecipient(array('test', 'test'));
		$instance->conduct($this->defaultData);
	}

	/**
	 * @test
	 */
	public function sendsEmail() {
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\EmailPipe');
		$message = $this->getMock('TYPO3\CMS\Core\Mail\MailMessage', array('send'));
		$message->expects($this->once())->method('send');
		$this->callInaccessibleMethod($instance, 'sendEmail', $message);
	}

	/**
	 * @test
	 */
	public function canGetAndSetSubject() {
		$this->assertGetterAndSetterWorks('subject', 'test', 'test', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetRecipient() {
		$this->assertGetterAndSetterWorks('recipient', 'test', 'test', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetSender() {
		$this->assertGetterAndSetterWorks('sender', 'test', 'test', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetBody() {
		$this->assertGetterAndSetterWorks('body', 'test', 'test', TRUE);
	}

}
