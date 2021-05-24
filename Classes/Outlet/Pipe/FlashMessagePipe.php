<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\Select;
use FluidTYPO3\Flux\Form\Field\Text;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;

/**
 * Pipe: Flash Message
 *
 * Sends a custom FlashMessage
 */
class FlashMessagePipe extends AbstractPipe implements PipeInterface
{

    const FLASHMESSAGE_QUEUE = 'extbase.flashmessages.flux';

    /**
     * @var integer
     */
    protected $severity = FlashMessage::OK;

    /**
     * @var boolean
     */
    protected $storeInSession = true;

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
     * @return mixed
     */
    public function conduct($data)
    {
        $queue = new FlashMessageQueue(static::FLASHMESSAGE_QUEUE);
        $flashMessage = new FlashMessage(
            (string) $this->getMessage(),
            (string) $this->getTitle(),
            (integer) $this->getSeverity(),
            (boolean) $this->getStoreInSession()
        );
        $queue->enqueue($flashMessage);
        return $data;
    }

    /**
     * @param integer $severity
     * @return FlashMessagePipe
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
        return $this;
    }

    /**
     * @return integer
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param boolean $storeInSession
     * @return FlashMessagePipe
     */
    public function setStoreInSession($storeInSession)
    {
        $this->storeInSession = $storeInSession;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getStoreInSession()
    {
        return $this->storeInSession;
    }

    /**
     * @param string $title
     * @return FlashMessagePipe
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $message
     * @return FlashMessagePipe
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
