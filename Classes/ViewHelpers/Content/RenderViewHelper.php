<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Content;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Renders all child content of a record based on the area name.
 *
 * The `area` is the `name` attribute of the `<grid.column>` that shall
 * be rendered.
 *
 * ### Example: Render all child elements of one grid column
 *
 * `fluidcontent` element with one column of child elements:
 *
 *     <f:section name="Configuration">
 *      <flux:grid>
 *       <flux:grid.row>
 *        <flux:grid.column name="teaser" colPos="0"/>
 *       </flux:grid.row>
 *      </flux:grid>
 *     </f:section>
 *
 *     <f:section name="Main">
 *      <div style="border: 1px solid red">
 *       <flux:content.render area="teaser"/>
 *      </div>
 *     </f:section>
 */
class RenderViewHelper extends GetViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $content = parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
        if (is_array($content)) {
            return implode(PHP_EOL, $content);
        }
        return $content;
    }
}
