<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SiteConfigurationProviderItems
{
    private ContentTypeManager $contentTypeManager;
    private PageService $pageService;

    public function __construct(ContentTypeManager $contentTypeManager, PageService $pageService)
    {
        $this->contentTypeManager = $contentTypeManager;
        $this->pageService = $pageService;
    }

    public function processContentTypeItems(array $tca, TcaSelectItems $bar): array
    {
        foreach ($this->contentTypeManager->fetchContentTypeNames() as $contentTypeName) {
            $tca['items'][] = [
                $contentTypeName,
                $contentTypeName,
            ];
        }
        return $tca;
    }

    public function processPageTemplateItems(array $tca, TcaSelectItems $bar): array
    {
        foreach ($this->pageService->getAvailablePageTemplateFiles() as $extensionName => $templateGroup) {
            foreach ($templateGroup as $form) {
                /** @var string|null $templateFilename */
                $templateFilename = $form->getOption(FormOption::TEMPLATE_FILE);
                if ($templateFilename === null) {
                    continue;
                }
                $label = $form->getLabel();
                $identity = $extensionName . '->' . lcfirst(pathinfo($templateFilename, PATHINFO_FILENAME));
                $label = $this->translate((string) $label, $extensionName) ?? $label ?? $identity;
                $tca['items'][] = [$label, $identity];
            }
        }
        return $tca;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function translate(string $label, string $extensionName): ?string
    {
        return LocalizationUtility::translate($label, ExtensionNamingUtility::getExtensionName($extensionName));
    }
}
