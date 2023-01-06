<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\PageService;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SiteConfigurationProviderItems
{
    public function processContentTypeItems(array $tca, TcaSelectItems $bar): array
    {
        /** @var ContentTypeManager $contentTypeManager */
        $contentTypeManager = GeneralUtility::makeInstance(ContentTypeManager::class);
        foreach ($contentTypeManager->fetchContentTypeNames() as $contentTypeName) {
            $tca['items'][] = [
                $contentTypeName,
                $contentTypeName,
            ];
        }
        return $tca;
    }

    public function processPageTemplateItems(array $tca, TcaSelectItems $bar): array
    {
        /** @var PageService $pageService */
        $pageService = GeneralUtility::makeInstance(PageService::class);
        foreach ($pageService->getAvailablePageTemplateFiles() as $extensionName => $templateGroup) {
            foreach ($templateGroup as $form) {
                /** @var string|null $templateFilename */
                $templateFilename = $form->getOption(Form::OPTION_TEMPLATEFILE);
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
        return LocalizationUtility::translate($label, $extensionName);
    }
}
