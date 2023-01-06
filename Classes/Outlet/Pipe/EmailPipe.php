<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Symfony\Component\Mime\Exception\RfcComplianceException;
use TYPO3\CMS\Core\Mail\MailMessage;

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

    protected string $subject = '';

    /**
     * @var string|array
     */
    protected $recipient;

    /**
     * @var string|array
     */
    protected $sender;

    protected ?string $body = null;

    /**
     * The name of a section that will be rendered using
     * the view set by the outlet and will be used instead of the body property
     */
    protected ?string $bodySection = null;

    /**
     * @param string|array $recipient
     */
    public function setRecipient($recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return string|array
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string $sender
     */
    public function setSender($sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string|array
     */
    public function getSender()
    {
        return $this->sender;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getBodySection(): ?string
    {
        return $this->bodySection;
    }

    public function setBodySection(?string $bodySection): self
    {
        $this->bodySection = $bodySection;
        return $this;
    }

    /**
     * @param array|string $data
     * @return array|string
     * @throws Exception
     */
    public function conduct($data)
    {
        try {
            $message = $this->prepareEmail($data);
            $this->sendEmail($message);
        } catch (RfcComplianceException $error) {
            throw new Exception($error->getMessage(), $error->getCode());
        }

        return $data;
    }

    /**
     * @param array|string $data
     */
    protected function prepareEmail($data): MailMessage
    {
        $body = $this->getBody();
        if (empty($body)) {
            if ($this->getBodySection() !== null && method_exists($this->view, 'renderSection')) {
                $body = $this->view->renderSection($this->getBodySection(), (array) $data, true);
            } else {
                $this->view->assignMultiple((array) $data);
                $body = $this->view->render();
            }
        }

        $sender = $this->getSender();
        $recipient = $this->getRecipient();
        if (is_array($recipient)) {
            [$recipientAddress, $recipientName] = $recipient;
        } else {
            $recipientAddress = $recipient;
            $recipientName = null;
        }
        if (is_array($sender)) {
            [$senderAddress, $senderName] = $sender;
        } else {
            $senderAddress = $sender;
            $senderName = null;
        }
        $subject = $this->getSubject();
        if (is_string($data)) {
            $body = $data;
        }
        $message = new MailMessage();
        $message->html($body);
        $message->subject($subject);
        $message->from(($senderName ?? $senderAddress) . ' <' . $senderAddress . '>');
        $message->to(($recipientName ?? $recipientAddress) . ' <' . $recipientAddress . '>');

        return $message;
    }

    protected function sendEmail(MailMessage $message): void
    {
        $message->send();
    }
}
