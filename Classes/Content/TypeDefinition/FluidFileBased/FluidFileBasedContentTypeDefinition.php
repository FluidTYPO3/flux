<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Fluid File-based Content Type Definition
 *
 * Class to hold the metadata required to operate a single
 * content type based on a Fluid template.
 */
class FluidFileBasedContentTypeDefinition implements FluidRenderingContentTypeDefinitionInterface
{
    protected $extensionIdentity = '';
    protected $basePath = '';
    protected $relativeFilePath = '';
    protected $providerClassName = Provider::class;

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
     * @param string $providerClassName Class name of a Flux ProviderInterface implementation that handles the content type
     */
    public function __construct(
        string $extensionIdentity,
        string $basePath,
        string $relativeFilePath,
        string $providerClassName = Provider::class
    ) {
        $this->extensionIdentity = $extensionIdentity;
        $this->basePath = $basePath;
        $this->relativeFilePath = $relativeFilePath;
        $this->providerClassName = $providerClassName;
    }

    public function getForm(array $record = []): Form\FormInterface
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(ProviderResolver::class)->resolvePrimaryConfigurationProvider(
            'tt_content',
            'pi_flexform',
            $record
        )->getForm($record);
    }

    public function getGrid(array $record = []): Form\Container\Grid
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(ProviderResolver::class)->resolvePrimaryConfigurationProvider(
            'tt_content',
            'pi_flexform',
            $record
        )->getGrid($record);
    }

    public static function fetchContentTypes(): iterable
    {
        return [];
    }

    public function getContentTypeName(): string
    {
        $path = pathinfo($this->relativeFilePath, PATHINFO_DIRNAME);
        $path = $path === '.' ? '' : $path . '_';
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($this->extensionIdentity));
        $contentReference = str_replace('/', '_', $path . pathinfo($this->relativeFilePath, PATHINFO_FILENAME));
        return $extensionSignature . '_' . strtolower($contentReference);
    }

    public function getIconReference(): string
    {
        return '';
    }

    public function getExtensionIdentity(): string
    {
        return $this->extensionIdentity;
    }

    public function getProviderClassName(): ?string
    {
        return $this->providerClassName;
    }

    public function isUsingTemplateFile(): bool
    {
        return true;
    }

    public function isUsingGeneratedTemplateSource(): bool
    {
        return false;
    }

    public function getTemplatePathAndFilename(): string
    {
        return $this->basePath . $this->relativeFilePath;
    }
}
