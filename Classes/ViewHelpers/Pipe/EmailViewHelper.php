<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\EmailPipe;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Email Outlet Pipe ViewHelper
 *
 * Adds an EmailPipe to the Form's Outlet
 */
class EmailViewHelper extends AbstractPipeViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('body', 'string', 'Message body. Can also be inserted as tag content');
        $this->registerArgument('bodySection', 'string', 'Section to use for the body');
        $this->registerArgument('subject', 'string', 'Message subject', true);
        $this->registerArgument(
            'recipient',
            'string',
            'Message recipient address or name+address as "Name <add@ress>"',
            true
        );
        $this->registerArgument(
            'sender',
            'string',
            'Message sender address or name+address as "Name <add@ress>"',
            true
        );
    }

    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        ?\Closure $renderChildrenClosure = null
    ): EmailPipe {
        /** @var array $arguments */
        $body = $arguments['body'];
        if (empty($body) && $renderChildrenClosure instanceof \Closure) {
            $body = $renderChildrenClosure();
        }
        /** @var EmailPipe $pipe */
        $pipe = GeneralUtility::makeInstance(EmailPipe::class);
        $pipe->setSubject((string) $arguments['subject']);
        $pipe->setSender($arguments['sender'] ?? '');
        $pipe->setRecipient($arguments['recipient'] ?? '');
        $pipe->setBody((string) $body);
        $pipe->setBodySection($arguments['bodySection'] ?? null);

        return $pipe;
    }
}
