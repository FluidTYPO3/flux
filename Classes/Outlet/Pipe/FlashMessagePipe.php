<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Pipe: Flash Message
 *
 * Sends a custom FlashMessage
 */
class FlashMessagePipe extends AbstractPipe implements PipeInterface
{
    const FLASHMESSAGE_QUEUE = 'extbase.flashmessages.flux';

    protected int $severity = FlashMessage::OK;
    protected bool $storeInSession = true;
    protected string $title = '';
    protected string $message = '';

    /**
     * @param array $data
     * @return mixed
     */
    public function conduct($data)
    {
        if (class_exists(ContextualFeedbackSeverity::class)) {
            $severity = ContextualFeedbackSeverity::from($this->getSeverity());
        } else {
            $severity = (integer) $this->getSeverity();
        }
        $queue = $this->getFlashMessageQueue();
        $flashMessage = new FlashMessage(
            (string) $this->getMessage(),
            (string) $this->getTitle(),
            $severity,
            (boolean) $this->getStoreInSession()
        );
        $queue->enqueue($flashMessage);
        return $data;
    }

    public function setSeverity(int $severity): self
    {
        $this->severity = $severity;
        return $this;
    }

    public function getSeverity(): int
    {
        return $this->severity;
    }

    public function setStoreInSession(bool $storeInSession): self
    {
        $this->storeInSession = $storeInSession;
        return $this;
    }

    public function getStoreInSession(): bool
    {
        return $this->storeInSession;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getFlashMessageQueue(): FlashMessageQueue
    {
        return new FlashMessageQueue(static::FLASHMESSAGE_QUEUE);
    }
}
