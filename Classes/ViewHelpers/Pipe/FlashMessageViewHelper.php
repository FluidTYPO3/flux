<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\FlashMessagePipe;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * FlashMessage Outlet Pipe ViewHelper
 *
 * Adds a FlashMessagePipe to the Form's Outlet
 */
class FlashMessageViewHelper extends AbstractPipeViewHelper
{
    public function initializeArguments(): void
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

    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        ?\Closure $renderChildrenClosure = null
    ): FlashMessagePipe {
        /** @var array $arguments */
        /** @var FlashMessagePipe $pipe */
        $pipe = GeneralUtility::makeInstance(FlashMessagePipe::class);
        $pipe->setTitle((string) $arguments['title']);
        $pipe->setMessage((string) $arguments['message']);
        $pipe->setSeverity((int) $arguments['severity']);
        $pipe->setStoreInSession((boolean) $arguments['storeInSession']);
        return $pipe;
    }
}
