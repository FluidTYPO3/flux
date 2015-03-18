<?php
namespace FluidTYPO3\Flux\ViewHelpers\Content;

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
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class RenderViewHelper extends GetViewHelper {

	/**
	 * Render
	 *
	 * @return string
	 */
	public function render() {
		$content = parent::render();
		if (TRUE === is_array($content)) {
			return implode(LF, $content);
		}
		return $content;
	}

}
