<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe\AbstractPipeTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class EmailPipeTest extends AbstractPipeTestCase {

	/**
	 * @var array
	 */
	protected $defaultData = ['subject' => 'Test subject'];

	/**
	 * @return EmailPipe
	 */
	protected function createInstance() {
		$pipe = $this->getMock('FluidTYPO3\Flux\Outlet\Pipe\EmailPipe', ['sendEmail']);
		ObjectAccess::setProperty($pipe, 'label', 'Mock EmailPipe', TRUE);
		return $pipe;
	}

	/**
	 * @test
	 */
	public function supportsSenderArray() {
		$instance = $this->createInstance();
		$instance->setSender(['test@test.com', 'test']);
		$output = $instance->conduct($this->defaultData);
		$this->assertNotEmpty($output);
	}

	/**
	 * @test
	 */
	public function supportsRecipientArray() {
		$instance = $this->createInstance();
		$instance->setRecipient(['test@test.com', 'test']);
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
		$instance->setRecipient(['test', 'test']);
		$instance->conduct($this->defaultData);
	}

	/**
	 * @test
	 */
	public function sendsEmail() {
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\EmailPipe');
		$message = $this->getMock('TYPO3\CMS\Core\Mail\MailMessage', ['send']);
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
