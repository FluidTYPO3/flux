<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Fetches a single variable from the template variables
 *
 * @package Flux
 * @subpackage ViewHelpers
 */
class VariableViewHelper extends AbstractFormViewHelper {

	/**
	 * @param string $name
	 * @return string
	 */
	public function render($name) {
		if (strpos($name, '.') === FALSE) {
			return $this->templateVariableContainer->get($name);
		} else {
			$parts = explode('.', $name);
			return ObjectAccess::getPropertyPath($this->templateVariableContainer->get(array_shift($parts)), implode('.', $parts));
		}
	}

}
