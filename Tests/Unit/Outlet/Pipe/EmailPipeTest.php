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
 * EmailPipeTest
 */
class EmailPipeTest extends AbstractPipeTestCase
{

    /**
     * @var array
     */
    protected $defaultData = array('subject' => 'Test subject');

    /**
     * @return EmailPipe
     */
    protected function createInstance()
    {
        $pipe = $this->getMockBuilder('FluidTYPO3\Flux\Outlet\Pipe\EmailPipe')->setMethods(array('sendEmail'))->getMock();
        ObjectAccess::setProperty($pipe, 'label', 'Mock EmailPipe', true);
        return $pipe;
    }

    /**
     * @test
     */
    public function supportsSenderArray()
    {
        $instance = $this->createInstance();
        $instance->setSender(array('test@test.com', 'test'));
        $output = $instance->conduct($this->defaultData);
        $this->assertNotEmpty($output);
    }

    /**
     * @test
     */
    public function supportsRecipientArray()
    {
        $instance = $this->createInstance();
        $instance->setRecipient(array('test@test.com', 'test'));
        $output = $instance->conduct($this->defaultData);
        $this->assertNotEmpty($output);
    }

    /**
     * @test
     */
    public function supportsStringData()
    {
        $instance = $this->createInstance();
        $output = $instance->conduct('test');
        $this->assertNotEmpty($output);
    }

    /**
     * @test
     */
    public function turnsMailboxValidationErrorIntoPipeException()
    {
        $instance = $this->createInstance();
        $this->setExpectedException('FluidTYPO3\Flux\Outlet\Pipe\Exception');
        $instance->setRecipient(array('test', 'test'));
        $instance->conduct($this->defaultData);
    }

    /**
     * @test
     */
    public function sendsEmail()
    {
        $instance = $this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\EmailPipe');
        $message = $this->getMockBuilder('TYPO3\CMS\Core\Mail\MailMessage')->setMethods(array('send'))->getMock();
        $message->expects($this->once())->method('send');
        $this->callInaccessibleMethod($instance, 'sendEmail', $message);
    }

    /**
     * @test
     */
    public function canGetAndSetSubject()
    {
        $this->assertGetterAndSetterWorks('subject', 'test', 'test', true);
    }

    /**
     * @test
     */
    public function canGetAndSetRecipient()
    {
        $this->assertGetterAndSetterWorks('recipient', 'test', 'test', true);
    }

    /**
     * @test
     */
    public function canGetAndSetSender()
    {
        $this->assertGetterAndSetterWorks('sender', 'test', 'test', true);
    }

    /**
     * @test
     */
    public function canGetAndSetBody()
    {
        $this->assertGetterAndSetterWorks('body', 'test', 'test', true);
    }
}
