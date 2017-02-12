<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\ContentService;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * MiscellaneousUtility Utility
 */
class MiscellaneousUtility
{

    /** Overhead used by unique integer generation. */
    const UNIQUE_INTEGER_OVERHEAD = ContentService::COLPOS_FLUXCONTENT;

    /**
     * @var array
     */
    private static $allowedIconTypes = ['svg', 'png', 'gif'];

    /**
     * @var IconFactory
     */
    private static $iconFactory;

    /**
     * @param integer $contentElementUid
     * @param string $areaName
     * @return integer
     */
    public static function generateUniqueIntegerForFluxArea($contentElementUid, $areaName)
    {
        $integers = array_map('ord', str_split($areaName));
        $integers[] = $contentElementUid;
        $integers[] = self::UNIQUE_INTEGER_OVERHEAD;
        $integer = array_sum($integers);
        // Loop + increment until a free position is found. The integer size is kept low by the logic above, but
        // it also means that collisions are possible. This iteration prevents such collisions.
        while (isset($_SESSION['target' . $integer])) {
            $integer++;
        }
        return $integer;
    }

    /**
     * @param string $icon
     * @return string
     */
    public static function getIcon($icon)
    {
        if (null === static::$iconFactory) {
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
    public static function wrapLink($inner, $uri, $title)
    {
        return '<a href="#" class="btn btn-default btn-sm" onclick="window.location.href=\'' . htmlspecialchars($uri) .
            '\'" title="' . $title . '">' . $inner . '</a>';
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
    public static function getIconForTemplate(Form $form)
    {
        if (true === $form->hasOption(Form::OPTION_ICON)) {
            return $form->getOption(Form::OPTION_ICON);
        }
        if (true === $form->hasOption(Form::OPTION_TEMPLATEFILE)) {
            $extensionKey = ExtensionNamingUtility::getExtensionKey($form->getExtensionName());
            $fullTemplatePathAndName = $form->getOption(Form::OPTION_TEMPLATEFILE);
            $templatePathParts = explode('/', $fullTemplatePathAndName);
            $templateName = pathinfo(array_pop($templatePathParts), PATHINFO_FILENAME);
            $controllerName = array_pop($templatePathParts);
            $iconFolder = ExtensionManagementUtility::extPath(
                $extensionKey,
                'Resources/Public/Icons/' . $controllerName . '/'
            );
            $iconAbsoluteUrl = '/' . str_replace(PATH_site, '', $iconFolder);
            $iconPathAndName = $iconFolder . $templateName;
            $filesInFolder = array();
            if (true === is_dir($iconFolder)) {
                if (true === defined(GLOB_BRACE)) {
                    $allowedExtensions = implode(',', self::$allowedIconTypes);
                    $iconMatchPattern = $iconPathAndName . '.{' . $allowedExtensions . '}';
                    $filesInFolder = glob($iconMatchPattern, GLOB_BRACE);
                } else {
                    foreach (self::$allowedIconTypes as $allowedIconType) {
                        $filesInFolder = array_merge($filesInFolder, glob($iconPathAndName . '.' . $allowedIconType));
                    }
                }
            }
            $iconFile = (is_array($filesInFolder) && 0 < count($filesInFolder) ? reset($filesInFolder) : null);
            $iconRelPathAndFilename = $iconFile ? $iconAbsoluteUrl . str_replace($iconFolder, '', $iconFile) : null;
            return $iconRelPathAndFilename;
        }
        return null;
    }

    /**
     * Returns a generated icon file into typo3temp/pics
     * @param string $originalFile
     * @param integer $width
     * @param integer $height
     * @return string
     */
    public static function createIcon($originalFile, $width, $height)
    {
        $extension = pathinfo($originalFile, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'svg':
            case 'svgz':
                $iconProvider = SvgIconProvider::class;
                break;
            default:
                $iconProvider = BitmapIconProvider::class;
        }
        $iconIdentifier = 'icon-' . md5($originalFile);
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $iconRegistry->registerIcon($iconIdentifier, $iconProvider, ['source' => $originalFile, 'size' => Icon::SIZE_LARGE]);
        return $iconIdentifier;
    }

    /**
     * Cleans flex form XML, removing any field nodes identified
     * in $removals and trimming the result to avoid empty containers.
     *
     * @param string $xml
     * @param array $removals
     * @return string
     */
    public static function cleanFlexFormXml($xml, array $removals = [])
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $fieldNodesToRemove = [];
        foreach ($dom->getElementsByTagName('field') as $fieldNode) {
            /** @var \DOMElement $fieldNode */
            if (true === in_array($fieldNode->getAttribute('index'), $removals)) {
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
            $hasIdNode = false;
            if (0 < $containerNode->attributes->length) {
                // skip <el> tags reserved for other purposes by attributes; only allow pure <el> tags.
                continue;
            }
            foreach ($containerNode->childNodes as $fieldNodeInContainer) {
                /** @var \DOMElement $fieldNodeInContainer */
                if (false === $fieldNodeInContainer instanceof \DOMElement) {
                    continue;
                }
                $isFieldNode = ('field' === $fieldNodeInContainer->tagName);
                $isIdField = ('id' === $fieldNodeInContainer->getAttribute('index'));
                if ($isFieldNode && $isIdField) {
                    $hasIdNode = true;
                    break;
                }
            }
            if (false === $hasIdNode) {
                $idNode = $dom->createElement('field');
                $idIndexAttribute = $dom->createAttribute('index');
                $idIndexAttribute->nodeValue = 'id';
                $idNode->appendChild($idIndexAttribute);
                $valueNode = $dom->createElement('value');
                $valueIndexAttribute = $dom->createAttribute('index');
                $valueIndexAttribute->nodeValue = 'vDEF';
                $valueNode->appendChild($valueIndexAttribute);
                $valueNode->nodeValue = sha1(uniqid('container_', true));
                $idNode->appendChild($valueNode);
                $containerNode->appendChild($idNode);
            }
        }
        // Remove all sheets that no longer contain any fields.
        $nodesToBeRemoved = [];
        foreach ($dom->getElementsByTagName('sheet') as $sheetNode) {
            if (0 === $sheetNode->getElementsByTagName('field')->length) {
                $nodesToBeRemoved[] = $sheetNode;
            }
        }

        foreach ($nodesToBeRemoved as $node) {
            /** @var \DOMElement $node */
            $node->parentNode->removeChild($node);
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
