<?php
namespace FluidTYPO3\Flux\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * MiscellaneousUtility Utility
 *
 * @package Flux
 * @subpackage Utility
 */
class MiscellaneousUtility {

	/**
	 * @var array
	 */
	private static $allowedIconTypes = array('png', 'gif');

    /**
	* @param string $icon
	* @return string
	*/
	public static function getIcon($icon) {
		return IconUtility::getSpriteIcon($icon);
	}

	/**
	* @param string $inner
	* @param string $uri
	* @param string $title
	* @return string
	*/
	public static function wrapLink($inner, $uri, $title) {
		return '<a href="#" onclick="window.location.href=\'' . htmlspecialchars($uri) . '\'" title="' . $title . '">' . $inner . '</a>';
	}

	/**
	 * Returns the icon for a template
	 * - checks and returns if manually set as option or
	 * - checks and returns Icon if it exists by convention in
	 *   EXT:$extensionKey/Resources/Public/Icons/$controllerName/$templateName.(png|gif)
	 *
	 * @param Form $form
	 * @return string|FALSE
	 */
	public static function getIconForTemplate(Form $form) {
		if (TRUE === $form->hasOption(Form::OPTION_ICON)) {
			return $form->getOption(Form::OPTION_ICON);
		}
		if (TRUE === $form->hasOption(Form::OPTION_TEMPLATEFILE)) {
			$extensionKey = ExtensionNamingUtility::getExtensionKey($form->getExtensionName());
			$fullTemplatePathAndName = $form->getOption(Form::OPTION_TEMPLATEFILE);

			$templatePathParts = explode('/', substr($fullTemplatePathAndName, 0, strpos($fullTemplatePathAndName, '.')));
			$templateName = array_pop($templatePathParts);
			$controllerName = array_pop($templatePathParts);

			$positionOfResourceInTemplatePath = strpos($fullTemplatePathAndName, 'Resources/Private/Templates/');
			$iconPathAndName = substr($fullTemplatePathAndName, 0, $positionOfResourceInTemplatePath) . 'Resources/Public/Icons/' . $controllerName . '/' . $templateName;

			foreach (self::$allowedIconTypes as $iconType) {
				$potentialIcon = $iconPathAndName . '.' . $iconType;
				if (is_file($potentialIcon)) {
					$positionOfResourceInIconPath = strpos($potentialIcon, 'Resources/Public/Icons/');
					return '../' . ExtensionManagementUtility::siteRelPath($extensionKey) . substr($potentialIcon, $positionOfResourceInIconPath);
				}
			}
		}
		return FALSE;
	}
}
