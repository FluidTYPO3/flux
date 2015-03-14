<?php
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\EmailPipe;
use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;

/**
 * Email Outlet Pipe ViewHelper
 *
 * Adds an EmailPipe to the Form's Outlet
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class EmailViewHelper extends AbstractPipeViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('body', 'string', 'Message body. Can also be inserted as tag content');
		$this->registerArgument('subject', 'string', 'Message subject', TRUE);
		$this->registerArgument('recipient', 'string', 'Message recipient address or name+address as "Name <add@ress>"', TRUE);
		$this->registerArgument('sender', 'string', 'Message sender address or name+address as "Name <add@ress>"', TRUE);
	}

	/**
	 * @return PipeInterface
	 */
	protected function preparePipeInstance() {
		$body = $this->arguments['body'];
		if (TRUE === empty($body)) {
			$body = $this->renderChildren();
		}
		/** @var EmailPipe $pipe */
		$pipe = $this->objectManager->get('FluidTYPO3\\Flux\\Outlet\\Pipe\\EmailPipe');
		$pipe->setSubject($this->arguments['subject']);
		$pipe->setSender($this->arguments['sender']);
		$pipe->setRecipient($this->arguments['recipient']);
		$pipe->setBody($body);
		return $pipe;
	}

}
