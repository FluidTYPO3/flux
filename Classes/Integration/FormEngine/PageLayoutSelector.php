<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

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
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class PageLayoutSelector extends AbstractNode
{
    private const DEFAULT_ICON_WIDTH = 200;

    private static bool $assetsIncluded = false;

    private PageService $pageService;
    private PageRenderer $pageRenderer;
    private PackageManager $packageManager;

    public function __construct(?NodeFactory $nodeFactory = null, array $data = [])
    {
        if (property_exists($this, 'nodeFactory')) {
            $this->nodeFactory = $nodeFactory ?? GeneralUtility::makeInstance(NodeFactory::class);
        }
        $this->data = $data;
        $this->pageService = GeneralUtility::makeInstance(PageService::class);
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->packageManager = GeneralUtility::makeInstance(PackageManager::class);
    }

    public function render(): array
    {
        $this->attachAssets();

        $result = $this->initializeResultArray();

        $selectedValue = $this->data['databaseRow'][$this->data['fieldName']] ?? null;

        $fieldName = 'data' . $this->data['elementBaseName'];
        $height = $this->data['parameterArray']['fieldConf']['config']['iconHeight'] ?? self::DEFAULT_ICON_WIDTH;
        $renderTitle = $this->data['parameterArray']['fieldConf']['config']['titles'] ?? false;
        $renderDescription = $this->data['parameterArray']['fieldConf']['config']['descriptions'] ?? false;

        $templates = $this->pageService->getAvailablePageTemplateFiles();

        $html = [];

        $html[] = '<div>';
        $html[] = '<label>';
        $html[] = '<input type="radio" name="' .
            $fieldName .
            '" value=""' .
            (empty($selectedValue) ? ' checked="checked"' : '') .
            ' />';
        $html[] = 'Parent decides';
        $html[] = '</label>';

        foreach ($templates as $groupName => $group) {
            $extensionName = ExtensionNamingUtility::getExtensionName($groupName);
            $packageInfo = $this->packageManager->getPackage(ExtensionNamingUtility::getExtensionKey($groupName));

            $html[] = '<h2>' . ($packageInfo->getPackageMetaData()->getTitle() ?? $groupName) . '</h2>';
            $html[] = '<fieldset class="flux-page-layouts">';
            foreach ($group as $form) {
                $icon = $this->resolveIconForForm($form);

                /** @var string $templateName */
                $templateName = $form->getOption(FormOption::TEMPLATE_FILE_RELATIVE);
                $identifier = $groupName . '->' . lcfirst($templateName);

                $html[] = '<label' . ($selectedValue === $identifier ? ' class="selected"' : '') . '>';
                $html[] = '<input type="radio" name="' .
                    $fieldName .
                    '" value="' .
                    $identifier .
                    '"' .
                    ($selectedValue === $identifier ? ' checked="checked"' : '') .
                    ' />';
                $html[] = '<img src="' . $icon .'" style="height: ' . $height . 'px" />';
                if ($renderTitle && ($title = $form->getLabel())) {
                    $title = $this->translate((string) $title, $extensionName) ?? $templateName;
                    if (strpos($title, 'LLL:EXT:') !== 0) {
                        $html[] = '<h4>' . $title . '</h4>';
                    }
                }

                if ($renderDescription && ($description = $form->getDescription())) {
                    $description = $this->translate((string) $description, $extensionName) ?? $description;
                    if (strpos($description, 'LLL:EXT:') !== 0) {
                        $html[] = '<p>' . $description . '</p>';
                    }
                }

                $html[] = '</label>';
            }
            $html[] = '</fieldset>';
        }

        $html[] = '</div>';

        $result['html'] = implode(PHP_EOL, $html);

        return $result;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function resolveIconForForm(Form $form): string
    {
        $defaultIcon = 'EXT:flux/Resources/Public/Icons/Layout.svg';
        $icon = MiscellaneousUtility::getIconForTemplate($form) ?? $defaultIcon;

        if (!file_exists(GeneralUtility::getFileAbsFileName($icon))) {
            $icon = PathUtility::getPublicResourceWebPath($defaultIcon);
        } elseif (strpos($icon, 'EXT:') === 0) {
            $icon = PathUtility::getPublicResourceWebPath($icon);
        } elseif (($icon[0] ?? null) !== '/') {
            $icon = PathUtility::getAbsoluteWebPath($icon);
        }
        return $icon;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function translate(string $label, string $extensionName): ?string
    {
        return LocalizationUtility::translate($label, $extensionName);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function attachAssets(): void
    {
        if (!self::$assetsIncluded) {
            $this->pageRenderer->addCssFile('EXT:flux/Resources/Public/css/flux.css');

            self::$assetsIncluded = true;
        }
    }
}
