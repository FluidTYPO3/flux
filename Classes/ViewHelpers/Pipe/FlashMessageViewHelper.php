<?php
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\FlashMessagePipe;
use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * FlashMessage Outlet Pipe ViewHelper
 *
 * Adds a FlashMessagePipe to the Form's Outlet
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class FlashMessageViewHelper extends AbstractPipeViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('message', 'string', 'FlashMessage message body', TRUE);
		$this->registerArgument('title', 'string', 'FlashMessage title to use', FALSE, 'Message');
		$this->registerArgument('severity', 'integer', 'Severity level, as integer', FALSE, FlashMessage::OK);
		$this->registerArgument('storeInSession', 'boolean', 'Store message in sesssion. If FALSE, message only lives in POST. Default TRUE.', FALSE, TRUE);
	}

	/**
	 * @return PipeInterface
	 */
	protected function preparePipeInstance() {
		/** @var FlashMessagePipe $pipe */
		$pipe = $this->objectManager->get('FluidTYPO3\\Flux\\Outlet\\Pipe\\FlashMessagePipe');
		$pipe->setTitle($this->arguments['title']);
		$pipe->setMessage($this->arguments['message']);
		$pipe->setSeverity($this->arguments['severity']);
		$pipe->setStoreInSession((boolean) $this->arguments['storeInSession']);
		return $pipe;
	}

}
