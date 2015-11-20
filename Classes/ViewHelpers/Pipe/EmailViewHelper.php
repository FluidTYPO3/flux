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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Email Outlet Pipe ViewHelper
 *
 * Adds an EmailPipe to the Form's Outlet
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
	 * @param RenderingContextInterface $renderingContext
	 * @param array $arguments
	 * @return PipeInterface
	 */
	protected static function preparePipeInstance(
		RenderingContextInterface $renderingContext,
		array $arguments,
		\Closure $renderChildrenClosure = NULL
	) {
		$body = $arguments['body'];
		if (TRUE === empty($body) && $renderChildrenClosure instanceof \Closure) {
			$body = $renderChildrenClosure();
		}
		/** @var EmailPipe $pipe */
		$pipe = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')
			->get('FluidTYPO3\\Flux\\Outlet\\Pipe\\EmailPipe');
		$pipe->setSubject($arguments['subject']);
		$pipe->setSender($arguments['sender']);
		$pipe->setRecipient($arguments['recipient']);
		$pipe->setBody($body);
		return $pipe;
	}

}
