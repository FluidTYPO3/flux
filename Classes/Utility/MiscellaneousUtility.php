<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use DOMElement;
use DOMNode;
use DOMNodeList;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MiscellaneousUtility
{
    private static array $allowedIconTypes = ['svg', 'png', 'gif'];

    /**
     * Returns the icon for a template
     * - checks and returns if manually set as option or
     * - checks and returns Icon if it exists by convention in
     *   EXT:$extensionKey/Resources/Public/Icons/$controllerName/$templateName.(png|gif)
     */
    public static function getIconForTemplate(Form $form): ?string
    {
        if (true === $form->hasOption(FormOption::ICON)) {
            $iconOptionValue = $form->getOption(FormOption::ICON);
            return is_scalar($iconOptionValue) ? (string) $iconOptionValue : null;
        }
        if (true === $form->hasOption(FormOption::TEMPLATE_FILE)) {
            $extensionKey = ExtensionNamingUtility::getExtensionKey((string) $form->getExtensionName());
            $fullTemplatePathAndName = $form->getOption(FormOption::TEMPLATE_FILE);
            $templatePathParts = is_scalar($fullTemplatePathAndName)
                ? explode('/', (string) $fullTemplatePathAndName)
                : [];
            if (empty($templatePathParts)) {
                return null;
            }
            $templateName = pathinfo(array_pop($templatePathParts), PATHINFO_FILENAME);
            $controllerName = array_pop($templatePathParts);
            $relativeIconFolder = 'Resources/Public/Icons/' . $controllerName . '/';
            $iconFolder = ExtensionManagementUtility::extPath(
                $extensionKey,
                $relativeIconFolder
            );
            $iconPathAndName = $iconFolder . $templateName;
            $filesInFolder = array();
            if (true === is_dir($iconFolder)) {
                if (true === defined('GLOB_BRACE')) {
                    $allowedExtensions = implode(',', self::$allowedIconTypes);
                    $iconMatchPattern = $iconPathAndName . '.{' . $allowedExtensions . '}';
                    $filesInFolder = glob($iconMatchPattern, GLOB_BRACE);
                } else {
                    foreach (self::$allowedIconTypes as $allowedIconType) {
                        $filesInFolder = array_merge(
                            $filesInFolder,
                            glob($iconPathAndName . '.' . $allowedIconType) ?: []
                        );
                    }
                }
            }
            $iconFile = (is_array($filesInFolder) && 0 < count($filesInFolder) ? reset($filesInFolder) : null);
            $iconRelPathAndFilename = $iconFile
                ? 'EXT:' . $extensionKey . '/' . $relativeIconFolder . pathinfo($iconFile, PATHINFO_BASENAME)
                : null;
            return $iconRelPathAndFilename;
        }
        return null;
    }

    public static function createIconIdentifier(string $originalFile): string
    {
        return 'icon-' . md5($originalFile);
    }

    /**
     * Returns a generated icon file into typo3temp/pics
     */
    public static function createIcon(string $originalFile, ?string $identifier = null): string
    {
        /** @var IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        if ($iconRegistry->isRegistered($originalFile)) {
            return $originalFile;
        }

        if (strpos($originalFile, 'EXT:') === 0 || $originalFile[0] !== '/') {
            $originalFile = GeneralUtility::getFileAbsFileName($originalFile);
        }

        $extension = pathinfo($originalFile, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'svg':
            case 'svgz':
                $iconProvider = SvgIconProvider::class;
                break;
            default:
                $iconProvider = BitmapIconProvider::class;
        }

        $iconIdentifier = $identifier ?? self::createIconIdentifier($originalFile);
        $iconRegistry->registerIcon(
            $iconIdentifier,
            $iconProvider,
            ['source' => $originalFile, 'size' => Icon::SIZE_DEFAULT]
        );
        return $iconIdentifier;
    }

    /**
     * Cleans flex form XML, removing any field nodes identified
     * in $removals and trimming the result to avoid empty containers.
     */
    public static function cleanFlexFormXml(string $xml, array $removals = []): string
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $fieldNodesToRemove = [];
        foreach ($dom->getElementsByTagName('field') as $fieldNode) {
            /** @var DOMElement $fieldNode */
            if (true === in_array($fieldNode->getAttribute('index'), $removals)) {
                $fieldNodesToRemove[] = $fieldNode;
            }
        }

        foreach ($fieldNodesToRemove as $fieldNodeToRemove) {
            /** @var DOMNode $parent */
            $parent = $fieldNodeToRemove->parentNode;
            /** @var DOMElement $fieldNodeToRemove */
            $parent->removeChild($fieldNodeToRemove);
        }

        // Assign a hidden ID to all container-type nodes, making the value available in templates etc.
        foreach ($dom->getElementsByTagName('el') as $containerNode) {
            /** @var DOMElement $containerNode */
            $hasIdNode = false;
            if ($containerNode->attributes instanceof \DOMNamedNodeMap && 0 < count($containerNode->attributes)) {
                // skip <el> tags reserved for other purposes by attributes; only allow pure <el> tags.
                continue;
            }
            foreach ($containerNode->childNodes as $fieldNodeInContainer) {
                /** @var DOMNode $fieldNodeInContainer */
                if (false === $fieldNodeInContainer instanceof DOMElement) {
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
            /** @var DOMNode $parent */
            $parent = $node->parentNode;
            /** @var DOMElement $node */
            $parent->removeChild($node);
        }

        // Return empty string in case remaining flexform XML is all empty
        /** @var DOMNodeList $dataNodes */
        $dataNodes = $dom->getElementsByTagName('data');
        /** @var DOMElement $dataNode */
        $dataNode = $dataNodes->item(0);
        $elements = $dataNode->getElementsByTagName('sheet');
        if (0 === $elements->length) {
            return '';
        }
        $xml = (string) $dom->saveXML();
        // hack-like pruning of empty-named node inserted when removing objects from a previously populated Section
        $xml = (string) preg_replace('#<el index="el">\s*</el>#', '', $xml);
        $xml = (string) preg_replace('#<field index="[^"]*">\s*</field>#', '', $xml);
        return $xml;
    }
}
