<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * MiscellaneousUtility Utility
 */
class MiscellaneousUtility {

	/** Overhead used by unique integer generation. Allows 10 billion records before collision */
	const UNIQUE_INTEGER_OVERHEAD = 10000000000;

	/**
	 * @var array
	 */
	private static $allowedIconTypes = array('svg', 'png', 'gif');

	/**
	 * @var IconFactory
	 */
	private static $iconFactory;

	/**
	 * @param integer $contentElementUid
	 * @param string $areaName
	 * @return integer
	 */
	public static function generateUniqueIntegerForFluxArea($contentElementUid, $areaName) {
		$integers = array_map('ord', str_split($areaName));
		$integers[] = $contentElementUid;
		$integers[] = self::UNIQUE_INTEGER_OVERHEAD;
		return 0 - array_sum($integers);
	}

	/**
	 * @param string $icon
	 * @return string
	 */
	public static function getIcon($icon) {
		if (NULL === static::$iconFactory) {
			static::$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
		}

		return static::$iconFactory->getIcon($icon, Icon::SIZE_SMALL);
	}

	/**
	 * @param string $inner
	 * @param string $uri
	 * @param string $title
	 * @return string
	 */
	public static function wrapLink($inner, $uri, $title) {
		return '<a href="#" class="btn btn-default btn-sm" onclick="window.location.href=\'' . htmlspecialchars($uri) . '\'" title="' . $title . '">' . $inner . '</a>';
	}

	/**
	 * Returns the icon for a template
	 * - checks and returns if manually set as option or
	 * - checks and returns Icon if it exists by convention in
	 *   EXT:$extensionKey/Resources/Public/Icons/$controllerName/$templateName.(png|gif)
	 *
	 * @param Form $form
	 * @return string|NULL
	 */
	public static function getIconForTemplate(Form $form) {
		if (TRUE === $form->hasOption(Form::OPTION_ICON)) {
			return $form->getOption(Form::OPTION_ICON);
		}
		if (TRUE === $form->hasOption(Form::OPTION_TEMPLATEFILE)) {
			$extensionKey = ExtensionNamingUtility::getExtensionKey($form->getExtensionName());
			$fullTemplatePathAndName = $form->getOption(Form::OPTION_TEMPLATEFILE);
			$templatePathParts = explode('/', $fullTemplatePathAndName);
			$templateName = pathinfo(array_pop($templatePathParts), PATHINFO_FILENAME);
			$controllerName = array_pop($templatePathParts);
			$allowedExtensions = implode(',', self::$allowedIconTypes);
			$iconFolder = ExtensionManagementUtility::extPath($extensionKey, 'Resources/Public/Icons/' . $controllerName . '/');
			$iconAbsoluteUrl = '/' . str_replace(PATH_site, '', $iconFolder);
			$iconPathAndName = $iconFolder . $templateName;
			$iconMatchPattern = $iconPathAndName . '.{' . $allowedExtensions . '}';
			$filesInFolder = (TRUE === is_dir($iconFolder) ? glob($iconMatchPattern, GLOB_BRACE) : array());
			$iconFile = (TRUE === is_array($filesInFolder) && 0 < count($filesInFolder) ? reset($filesInFolder) : NULL);
			$iconRelPathAndFilename = (NULL !== $iconFile) ? $iconAbsoluteUrl . str_replace($iconFolder, '', $iconFile) : NULL;
			return $iconRelPathAndFilename;
		}
		return NULL;
	}

	/**
	 * Returns a generated icon file into typo3temp/pics
	 * @param string $originalFile
	 * @param integer $width
	 * @param integer $height
	 * @return string
	 */
	public static function createIcon($originalFile, $width, $height) {
		/** @var GraphicalFunctions $image */
		$image = GeneralUtility::makeInstance('TYPO3\CMS\Core\Imaging\GraphicalFunctions');
		$image->absPrefix = PATH_site;
		$image->png_truecolor = TRUE;
		$image->init();
		$newResource = $image->imageMagickConvert($originalFile, 'png', $width, $height, '', '', array(), TRUE);
		return str_replace(PATH_site, '/', $newResource[3]);
	}

	/**
	 * Cleans flex form XML, removing any field nodes identified
	 * in $removals and trimming the result to avoid empty containers.
	 *
	 * @param string $xml
	 * @param array $removals
	 * @return string
	 */
	public static function cleanFlexFormXml($xml, array $removals = array()) {
		$dom = new \DOMDocument();
		$dom->loadXML($xml);
		$dom->preserveWhiteSpace = FALSE;
		$dom->formatOutput = TRUE;
		$fieldNodesToRemove = array();
		foreach ($dom->getElementsByTagName('field') as $fieldNode) {
			/** @var \DOMElement $fieldNode */
			if (TRUE === in_array($fieldNode->getAttribute('index'), $removals)) {
				$fieldNodesToRemove[] = $fieldNode;
			}
		}

		foreach ($fieldNodesToRemove as $fieldNodeToRemove) {
			/** @var \DOMElement $fieldNodeToRemove */
			$fieldNodeToRemove->parentNode->removeChild($fieldNodeToRemove);
		}

		// Assign a hidden ID to all container-type nodes, making the value available in templates etc.
		foreach ($dom->getElementsByTagName('el') as $containerNode) {
			/** @var \DOMElement $containerNode */
			$hasIdNode = FALSE;
			if (0 < $containerNode->attributes->length) {
				// skip <el> tags reserved for other purposes by attributes; only allow pure <el> tags.
				continue;
			}
			foreach ($containerNode->childNodes as $fieldNodeInContainer) {
				/** @var \DOMElement $fieldNodeInContainer */
				if (FALSE === $fieldNodeInContainer instanceof \DOMElement) {
					continue;
				}
				$isFieldNode = ('field' === $fieldNodeInContainer->tagName);
				$isIdField = ('id' === $fieldNodeInContainer->getAttribute('index'));
				if ($isFieldNode && $isIdField) {
					$hasIdNode = TRUE;
					break;
				}
			}
			if (FALSE === $hasIdNode) {
				$idNode = $dom->createElement('field');
				$idIndexAttribute = $dom->createAttribute('index');
				$idIndexAttribute->nodeValue = 'id';
				$idNode->appendChild($idIndexAttribute);
				$valueNode = $dom->createElement('value');
				$valueIndexAttribute = $dom->createAttribute('index');
				$valueIndexAttribute->nodeValue = 'vDEF';
				$valueNode->appendChild($valueIndexAttribute);
				$valueNode->nodeValue = sha1(uniqid('container_', TRUE));
				$idNode->appendChild($valueNode);
				$containerNode->appendChild($idNode);
			}
		}
		// Remove all sheets that no longer contain any fields.
		foreach ($dom->getElementsByTagName('sheet') as $sheetNode) {
			if (0 === $sheetNode->getElementsByTagName('field')->length) {
				$sheetNode->parentNode->removeChild($sheetNode);
			}
		}
		// Return empty string in case remaining flexform XML is all empty
		$dataNode = $dom->getElementsByTagName('data')->item(0);
		if (0 === $dataNode->getElementsByTagName('sheet')->length) {
			return '';
		}
		$xml = $dom->saveXML();
		// hack-like pruning of empty-named node inserted when removing objects from a previously populated Section
		$xml = preg_replace('#<el index="el">\s*</el>#', '', $xml);
		$xml = preg_replace('#<field index="[^"]*">\s*</field>#', '', $xml);
		return $xml;
	}

}
