<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class PageLayoutDataProvider
{
    private ConfigurationManagerInterface $configurationManager;
    private PageService $pageService;
    private SiteFinder $siteFinder;

    public function __construct(
        ConfigurationManagerInterface $configurationManager,
        PageService $pageService,
        SiteFinder $siteFinder
    ) {
        $this->configurationManager = $configurationManager;
        $this->pageService = $pageService;
        $this->siteFinder = $siteFinder;
    }

    public function addItems(array $parameters): array
    {
        $typoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $settings = GeneralUtility::removeDotsFromTS((array) ($typoScript['plugin.']['tx_flux.'] ?? []));
        if (isset($settings['siteRootInheritance'])) {
            $hideInheritFieldSiteRoot = 1 > $settings['siteRootInheritance'];
        } else {
            $hideInheritFieldSiteRoot = false;
        }
        $pageIsSiteRoot = (boolean) ($parameters['row']['is_siteroot'] ?? false);
        $forceDisplayInheritSiteRoot = 'tx_fed_page_controller_action_sub' === ($parameters['field'] ?? null)
            && !$hideInheritFieldSiteRoot;
        $forceHideInherit = (0 === (int) ($parameters['row']['pid'] ?? 0));
        if (!$forceHideInherit) {
            if (!$pageIsSiteRoot || $forceDisplayInheritSiteRoot || !$hideInheritFieldSiteRoot) {
                $parameters['items'][] = [
                    'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action.default',
                    '',
                    'actions-move-down'
                ];
            }
        }

        $allowedTemplates = [];
        $pageUid = (int) ($parameters['row']['uid'] ?? 0);
        if ($pageUid > 0) {
            try {
                $site = $this->siteFinder->getSiteByPageId($pageUid);
                $siteConfiguration = $site->getConfiguration();
                $allowedTemplates = GeneralUtility::trimExplode(
                    ',',
                    $siteConfiguration['flux_page_templates'] ?? '',
                    true
                );
            } catch (SiteNotFoundException $exception) {
                $allowedTemplates = [];
            }
        }

        $availableTemplates = $this->pageService->getAvailablePageTemplateFiles();
        foreach ($availableTemplates as $extension => $group) {
            $parameters['items'] = array_merge(
                $parameters['items'],
                $this->renderOptions($extension, $group, $parameters, $allowedTemplates)
            );
        }

        return $parameters;
    }

    protected function renderOptions(string $extension, array $group, array $parameters, array $allowedTemplates): array
    {
        $options = [];
        if (!empty($group)) {
            $extensionKey = ExtensionNamingUtility::getExtensionKey($extension);
            $groupTitle = $this->getGroupTitle($extensionKey);

            $templateOptions = [];
            foreach ($group as $form) {
                $optionArray = $this->renderOption($form, $parameters);
                if (!empty($allowedTemplates) && !in_array($optionArray[1], $allowedTemplates)) {
                    continue;
                }
                $templateOptions[] = $optionArray;
            }
            if (!empty($templateOptions)) {
                $options = array_merge($options, [[$groupTitle, '--div--']], $templateOptions);
            }
        }
        return $options;
    }

    protected function renderOption(Form $form, array $parameters): array
    {
        $extension = $form->getExtensionName();
        $thumbnail = MiscellaneousUtility::getIconForTemplate($form);
        if ($thumbnail) {
            $thumbnail = ltrim($thumbnail, '/');
            $thumbnail = GeneralUtility::getFileAbsFileName($thumbnail);
            $thumbnail = $thumbnail ? MiscellaneousUtility::createIcon($thumbnail) : null;
        }
        /** @var string|null $template */
        $template = $form->getOption(FormOption::TEMPLATE_FILE_RELATIVE);
        if ($template === null) {
            return [];
        }
        $label = $form->getLabel();
        $optionValue = $extension . '->' . lcfirst($template);
        $option = [$label, $optionValue, $thumbnail];
        return $option;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getGroupTitle(string $extensionKey): string
    {
        if (!$this->isExtensionLoaded($extensionKey)) {
            $groupTitle = ucfirst($extensionKey);
        } else {
            $emConfigFile = ExtensionManagementUtility::extPath($extensionKey, 'ext_emconf.php');
            $_EXTKEY = $extensionKey;
            require $emConfigFile;
            $groupTitle = reset($EM_CONF)['title'];
        }
        return $groupTitle;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function isExtensionLoaded(string $extensionKey): bool
    {
        return ExtensionManagementUtility::isLoaded($extensionKey);
    }
}
