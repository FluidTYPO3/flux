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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * FlashMessage Outlet Pipe ViewHelper
 *
 * Adds a FlashMessagePipe to the Form's Outlet
 */
class FlashMessageViewHelper extends AbstractPipeViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('message', 'string', 'FlashMessage message body', true);
        $this->registerArgument('title', 'string', 'FlashMessage title to use', false, 'Message');
        $this->registerArgument('severity', 'integer', 'Severity level, as integer', false, FlashMessage::OK);
        $this->registerArgument(
            'storeInSession',
            'boolean',
            'Store message in sesssion. If FALSE, message only lives in POST. Default TRUE.',
            false,
            true
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @return PipeInterface
     */
    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        array $arguments,
        \Closure $renderChildrenClosure = null
    ) {
        /** @var FlashMessagePipe $pipe */
        $pipe = GeneralUtility::makeInstance(ObjectManager::class)->get(FlashMessagePipe::class);
        $pipe->setTitle($arguments['title']);
        $pipe->setMessage($arguments['message']);
        $pipe->setSeverity($arguments['severity']);
        $pipe->setStoreInSession((boolean) $arguments['storeInSession']);
        return $pipe;
    }
}
