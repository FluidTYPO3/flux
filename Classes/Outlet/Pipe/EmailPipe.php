<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Symfony\Component\Mime\Email;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Email Pipe
 *
 * Sends an email with a dump of the data in its current state.
 * Chain with other Pipes to convert data before it reaches this
 * Pipe if you want to - just as an example - create a proper
 * email body text containing a nice representaton of the data.
 */
class EmailPipe extends AbstractPipe implements PipeInterface, ViewAwarePipeInterface
{

    use ViewAwarePipeTrait;

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
     * @var string|null
     */
    protected $body = null;

    /**
     * The name of a section that will be rendered using
     * the view set by the outlet and will be used instead of the body property
     *
     * @var string|null
     */
    protected $bodySection = null;

    /**
     * @param string $recipient
     * @return EmailPipe
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string $sender
     * @return EmailPipe
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $subject
     * @return EmailPipe
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string|null $body
     * @return EmailPipe
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return null|string
     */
    public function getBodySection()
    {
        return $this->bodySection;
    }

    /**
     * @param null|string $bodySection
     */
    public function setBodySection($bodySection)
    {
        $this->bodySection = $bodySection;
    }

    /**
     * @param mixed $data
     * @return mixed
     * @throws Exception
     */
    public function conduct($data)
    {
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
     * @return MailMessage|Email
     */
    protected function prepareEmail($data)
    {
        $body = null;
        if ($this->getBodySection() !== null) {
            $body = $this->view->renderSection($this->getBodySection(), $data, true);
        }
        if (empty($body)) {
            $body = $this->getBody();
        }
        $sender = $this->getSender();
        $recipient = $this->getRecipient();
        if (true === is_array($recipient)) {
            list ($recipientAddress, $recipientName) = $recipient;
        } else {
            $recipientAddress = $recipient;
            $recipientName = null;
        }
        if (true === is_array($sender)) {
            list ($senderAddress, $senderName) = $sender;
        } else {
            $senderAddress = $sender;
            $senderName = null;
        }
        $subject = $this->getSubject();
        if (true === is_string($data)) {
            $body = $data;
        }
        $message = new MailMessage();
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '10.4', '>=')) {
            $message->html($body);
            $message->subject($subject);
            $message->from($sender . ' <' . $senderAddress . '>');
            $message->to($recipient . ' <' . $recipientAddress . '>');
        } else {
            $message->setBody($body);
            $message->setSubject($subject);
            $message->setFrom($senderAddress, $senderName);
            $message->setTo($recipientAddress, $recipientName);
        }

        return $message;
    }

    /**
     * @param MailMessage $message
     * @return void
     */
    protected function sendEmail($message)
    {
        $message->send();
    }
}
