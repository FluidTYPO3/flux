<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\PageService;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SiteConfigurationProviderItems
{
    public function processContentTypeItems(array $tca, TcaSelectItems $bar): array
    {
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
        $pageService = GeneralUtility::makeInstance(ObjectManager::class)->get(PageService::class);
        foreach ($pageService->getAvailablePageTemplateFiles() as $extensionName => $templateGroup) {
            foreach ($templateGroup as $form) {
                $templateFilename = $form->getOption(Form::OPTION_TEMPLATEFILE);
                $label = $form->getLabel();
                $identity = $extensionName . '->' . lcfirst(pathinfo($templateFilename, PATHINFO_FILENAME));
                try {
                    $label = LocalizationUtility::translate($label) ?: $identity;
                } catch (\InvalidArgumentException $error) {
                    $label = $identity;
                }
                $tca['items'][] = [$label, $identity];
            }
        }
        return $tca;
    }
}
