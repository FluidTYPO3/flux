<?php
namespace FluidTYPO3\Flux\ViewHelpers\Content;

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * ### Content: Render ViewHelper
 *
 * Renders all child content of a record based on area.
 */
class RenderViewHelper extends GetViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Default implementation for use in compiled templates
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $content = parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
        if (true === is_array($content)) {
            return implode(LF, $content);
        }
        return $content;
    }
}
