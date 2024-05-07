<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Drop-in Fluid Content Type Definition
 *
 * Holds one set of metadata to operate a single content type
 * based on a template file located in the site-wide, drop-in
 * resources location.
 */
class DropInContentTypeDefinition extends FluidFileBasedContentTypeDefinition
{
    public const DESIGN_DIRECTORY = 'design/';
    public const TEMPLATES_DIRECTORY = 'Templates/';
    public const PARTIALS_DIRECTORY = 'Partials/';
    public const LAYOUTS_DIRECTORY = 'Layouts/';
    public const TEMPLATES_PATTERN = '*.html';
    public const CONTENT_DIRECTORY = 'Content/';
    public const PAGE_DIRECTORY = 'Page/';

    protected string $extensionIdentity = 'FluidTYPO3.Flux';
    protected string $basePath = 'design/';
    protected string $relativeFilePath = '';
    protected string $providerClassName = Provider::class;

    /**
     * Constructs a Fluid file-based content type definition
     *
     * Can be used to construct definitions based on template files
     * which contain Flux form definitions and supports sub-folders
     * for template files by specifying $relativeFilePath as a path
     * inside a folder relative to the $basePath.
     *
     * @param string $extensionIdentity The VendorName.ExtensionName identity of the extension that contains the file
     * @param string $basePath Absolute path, or EXT:... path to location of template file
     * @param string $relativeFilePath Path of file relative to $basePath, without leading slash
     * @param string $providerClassName Class name of a Flux ProviderInterface implementation that handles the CType
     */
    public function __construct(
        string $extensionIdentity,
        string $basePath,
        string $relativeFilePath,
        string $providerClassName = Provider::class
    ) {
        $this->extensionIdentity = $extensionIdentity;
        $this->basePath = static::determineAbsolutePathForFilename($basePath);
        $this->relativeFilePath = $relativeFilePath;
        $this->providerClassName = $providerClassName;
    }

    /**
     * @return iterable|DropInContentTypeDefinition[]
     */
    public static function fetchContentTypes(): iterable
    {
        if (!ExtensionConfigurationUtility::getOption(ExtensionOption::OPTION_PLUG_AND_PLAY)) {
            // Do not return or auto-create any plug and play templates if the extension config option is disabled.
            // Option default value is ENABLED (hence null coalesce to TRUE if not defined)
            return [];
        }
        // Steps:
        // 1) auto-create if missing, the required file structure and dummy files
        // 2) iterate all content types found in the file structure
        $plugAndPlayDirectory = ExtensionConfigurationUtility::getOption(
            ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY
        );
        if (!is_scalar($plugAndPlayDirectory)) {
            return [];
        }
        $basePath = trim((string) $plugAndPlayDirectory, '/.');
        if (empty($basePath)) {
            return [];
        }
        $basePath = static::determineAbsolutePathForFilename($basePath) . '/';
        static::initializeDropInFileSystemStructure($basePath);

        $contentTypesPath = $basePath . static::TEMPLATES_DIRECTORY . static::CONTENT_DIRECTORY;
        /** @var Finder $finder */
        $finder = GeneralUtility::makeInstance(Finder::class);
        try {
            /** @var \SplFileInfo[] $files */
            $files = $finder->in($contentTypesPath)->name(static::TEMPLATES_PATTERN)->sortByName();
        } catch (DirectoryNotFoundException $exception) {
            return [];
        }
        $types = [];
        $basePathLength = strlen($contentTypesPath);
        foreach ($files as $file) {
            $templateFile = $file->getPath() . '/' . $file->getFilename();
            // May cause some files to be ignored if the files are either symlinked or the base path was not possible
            // to resolve correctly. This can happen if for some reason, ENV is configured with a public path that is
            // not within the project path, is is configured as an absolute path (which technically isn't correct).
            // We ignore this case instead of throwing an exception - essentially disabling drop-in templates on systems
            // which contain an unexpected public path.
            if (strlen($templateFile) > $basePathLength
                && substr_compare($basePath, $templateFile, 0, $basePathLength)
            ) {
                $relativeTemplatePath = substr($templateFile, $basePathLength);
                $contentType = new DropInContentTypeDefinition(
                    'FluidTYPO3.Flux',
                    $contentTypesPath,
                    $relativeTemplatePath
                );
                $types[$contentType->getContentTypeName()] = $contentType;
            }
        }
        return $types;
    }

    public function getIconReference(): string
    {
        return '';
    }

    public function getProviderClassName(): ?string
    {
        return $this->providerClassName;
    }

    protected static function initializeDropInFileSystemStructure(string $basePath): void
    {
        if (!file_exists($basePath)) {
            static::createDir($basePath . static::PARTIALS_DIRECTORY);
            static::createDir(
                $basePath . static::LAYOUTS_DIRECTORY,
                ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/Default.html')
            );
            static::createDir(
                $basePath . static::TEMPLATES_DIRECTORY . static::CONTENT_DIRECTORY,
                ExtensionManagementUtility::extPath('flux', 'Resources/Private/Skeletons/Content/Standard.html')
            );
            static::createDir(
                $basePath . static::TEMPLATES_DIRECTORY . static::PAGE_DIRECTORY,
                ExtensionManagementUtility::extPath('flux', 'Resources/Private/Skeletons/Page/Standard.html')
            );
        }
    }

    protected static function createDir(string $directory, ?string $sourceFile = null): void
    {
        mkdir($directory, 0775, true);
        if ($sourceFile) {
            copy(
                $sourceFile,
                $directory . pathinfo($sourceFile, PATHINFO_BASENAME)
            );
        }
    }

    protected static function determineAbsolutePathForFilename(string $filename): string
    {
        if (strpos($filename, '://') !== false) {
            return $filename;
        }
        if (strpos($filename, '/') === 0) {
            return $filename;
        }
        return Environment::getPublicPath() . '/' . $filename;
    }
}
