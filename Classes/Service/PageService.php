<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Proxy\TemplatePathsProxy;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * Page Service
 *
 * Service for interacting with Pages - gets content elements and page configuration
 * options.
 */
class PageService implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected WorkspacesAwareRecordService $workspacesAwareRecordService;
    protected FrontendInterface $runtimeCache;

    public function __construct(
        WorkspacesAwareRecordService $recordService,
        CacheManager $cacheManager
    ) {
        $this->workspacesAwareRecordService = $recordService;
        $this->runtimeCache = $cacheManager->getCache('runtime');
    }

    /**
     * Process RootLine to find first usable, configured Fluid Page Template.
     * WARNING: do NOT use the output of this feature to overwrite $row - the
     * record returned may or may not be the same record as defined in $id.
     *
     * @api
     */
    public function getPageTemplateConfiguration(int $pageUid, bool $pageUidIsParentUid = false): ?array
    {
        $pageUid = (integer) $pageUid;
        if (!$pageUid) {
            return null;
        }
        $cacheId = 'flux-template-configuration-' . $pageUid;
        /** @var array|null $fromCache */
        $fromCache = $this->runtimeCache->get($cacheId);
        if ($fromCache) {
            return $fromCache;
        }

        $resolvedMainTemplateIdentity = null;
        $resolvedSubTemplateIdentity = null;
        $recordDefiningMain = null;
        $recordDefiningSub = null;
        $rootLine = $this->getRootLine($pageUid);

        // Initialize with possibly-empty values and loop root line
        // to fill values as they are detected.
        foreach ($rootLine as $page) {
            $rootLinePageUid = (integer) ($page['uid'] ?? 0);
            $mainFieldValue = $page[PageProvider::FIELD_ACTION_MAIN] ?? null;
            $subFieldValue = $page[PageProvider::FIELD_ACTION_SUB] ?? null;
            $resolvedMainTemplateIdentity = is_array($mainFieldValue) ? $mainFieldValue[0] : $mainFieldValue;
            $resolvedSubTemplateIdentity = is_array($subFieldValue) ? $subFieldValue[0] : $subFieldValue;
            $containsSubDefinition = (strpos($subFieldValue ?? '', '->') !== false);
            $isCandidate = $pageUidIsParentUid ? true : $rootLinePageUid !== $pageUid;
            if ($containsSubDefinition && $isCandidate) {
                $resolvedSubTemplateIdentity = $subFieldValue;
                $recordDefiningSub = $page;
                if (empty($resolvedMainTemplateIdentity)) {
                    // Conditions met: current page is not $pageUid, original page did not
                    // contain a "this page" layout, current rootline page has "sub" selection.
                    // Then, set our "this page" value to use the "sub" selection that was detected.
                    $resolvedMainTemplateIdentity = $resolvedSubTemplateIdentity;
                    $recordDefiningMain = $page;
                }
                break;
            }
        };
        if (empty($resolvedMainTemplateIdentity) && empty($resolvedSubTemplateIdentity)) {
            // Neither directly configured "this page" nor inherited "sub" contains a valid value;
            // no configuration was detected at all.
            return null;
        }
        $configuration = [
            'tx_fed_page_controller_action' => $resolvedMainTemplateIdentity,
            'tx_fed_page_controller_action_sub' => $resolvedSubTemplateIdentity,
            'record_main' => $recordDefiningMain,
            'record_sub' => $recordDefiningSub,
        ];
        $this->runtimeCache->set($cacheId, $configuration);
        return $configuration;
    }

    /**
     * Get a usable page configuration flexform from rootline
     *
     * @api
     */
    public function getPageFlexFormSource(int $pageUid): ?string
    {
        $pageUid = (integer) $pageUid;
        if (!$pageUid) {
            return null;
        }
        $fieldList = 'uid,pid,t3ver_oid,tx_fed_page_flexform';
        $page = $this->workspacesAwareRecordService->getSingle('pages', $fieldList, $pageUid);
        while ($page !== null && 0 !== (integer) $page['uid'] && empty($page['tx_fed_page_flexform'])) {
            $resolveParentPageUid = (integer) (0 > $page['pid'] ? $page['t3ver_oid'] : $page['pid']);
            $page = $this->workspacesAwareRecordService->getSingle('pages', $fieldList, $resolveParentPageUid);
        }
        return $page['tx_fed_page_flexform'] ?? null;
    }

    public function getPageConfiguration(?string $extensionName = null): array
    {
        if (null !== $extensionName && true === empty($extensionName)) {
            // Note: a NULL extensionName means "fetch ALL defined collections" whereas
            // an empty value that is not null indicates an incorrect caller. Instead
            // of returning ALL paths here, an empty array is the proper return value.
            // However, dispatch a debug message to inform integrators of the problem.
            if ($this->logger instanceof LoggerInterface) {
                $this->logger->log(
                    'notice',
                    'Template paths have been attempted fetched using an empty value that is NOT NULL in ' .
                    get_class($this) . '. This indicates a potential problem with your TypoScript configuration - a ' .
                    'value which is expected to be an array may be defined as a string. This error is not fatal but ' .
                    'may prevent the affected collection (which cannot be identified here) from showing up'
                );
            }
            return [];
        }

        $plugAndPlayEnabled = ExtensionConfigurationUtility::getOption(
            ExtensionOption::OPTION_PLUG_AND_PLAY
        );
        $plugAndPlayDirectory = ExtensionConfigurationUtility::getOption(
            ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY
        );
        if (!is_scalar($plugAndPlayDirectory)) {
            return [];
        }
        $plugAndPlayTemplatesDirectory = trim((string) $plugAndPlayDirectory, '/.') . '/';
        if ($plugAndPlayEnabled && $extensionName === 'Flux') {
            return [
                TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [
                    $plugAndPlayTemplatesDirectory
                    . DropInContentTypeDefinition::TEMPLATES_DIRECTORY
                    . DropInContentTypeDefinition::PAGE_DIRECTORY
                ],
                TemplatePaths::CONFIG_PARTIALROOTPATHS => [
                    $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::PARTIALS_DIRECTORY
                ],
                TemplatePaths::CONFIG_LAYOUTROOTPATHS => [
                    $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::LAYOUTS_DIRECTORY
                ],
            ];
        }
        if (null !== $extensionName) {
            $templatePaths = $this->createTemplatePaths($extensionName);
            return TemplatePathsProxy::toArray($templatePaths);
        }
        $configurations = [];
        $registeredExtensionKeys = Core::getRegisteredProviderExtensionKeys('Page');
        foreach ($registeredExtensionKeys as $registeredExtensionKey) {
            $templatePaths = $this->createTemplatePaths($registeredExtensionKey);
            $configurations[$registeredExtensionKey] = TemplatePathsProxy::toArray($templatePaths);
        }
        if ($plugAndPlayEnabled) {
            $configurations['FluidTYPO3.Flux'] = array_replace(
                $configurations['FluidTYPO3.Flux'] ?? [],
                [
                    TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [
                        $plugAndPlayTemplatesDirectory
                        . DropInContentTypeDefinition::TEMPLATES_DIRECTORY
                        . DropInContentTypeDefinition::PAGE_DIRECTORY
                    ],
                    TemplatePaths::CONFIG_PARTIALROOTPATHS => [
                        $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::PARTIALS_DIRECTORY
                    ],
                    TemplatePaths::CONFIG_LAYOUTROOTPATHS => [
                        $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::LAYOUTS_DIRECTORY
                    ],
                ]
            );
        }
        return $configurations;
    }

    /**
     * Gets a list of usable Page Templates from defined page template TypoScript.
     * Returns a list of Form instances indexed by the path ot the template file.
     *
     * @return Form[][]
     * @api
     */
    public function getAvailablePageTemplateFiles(): array
    {
        $cacheKey = 'page_templates';
        /** @var array|null $fromCache */
        $fromCache = $this->runtimeCache->get($cacheKey);
        if ($fromCache) {
            return $fromCache;
        }
        $typoScript = $this->getPageConfiguration();
        $output = [];

        foreach ((array) $typoScript as $extensionName => $group) {
            if (!($group['enable'] ?? true)) {
                continue;
            }
            $output[$extensionName] = [];
            $templatePaths = $this->createTemplatePaths($extensionName);
            $finder = Finder::create()->in($templatePaths->getTemplateRootPaths())->name('*.html')->sortByName();
            foreach ($finder->files() as $file) {
                /** @var \SplFileInfo $file */
                if (substr($file->getBasename(), 0, 1) === '.') {
                    continue;
                }
                if (strpos($file->getPath(), DIRECTORY_SEPARATOR . 'Page') === false) {
                    continue;
                }
                $filename = $file->getRelativePathname();
                if (isset($output[$extensionName][$filename])) {
                    continue;
                }

                $view = $this->createViewInstance($extensionName, $templatePaths, $file);
                try {
                    $view->renderSection('Configuration');
                    $form = $view->getRenderingContext()
                        ->getViewHelperVariableContainer()
                        ->get(FormViewHelper::class, 'form');

                    if (!$form instanceof Form) {
                        if ($this->logger instanceof LoggerInterface) {
                            $this->logger->log(
                                'error',
                                'Template file ' . $file . ' contains an unparsable Form definition'
                            );
                        }
                        continue;
                    } elseif (!$form->getEnabled()) {
                        if ($this->logger instanceof LoggerInterface) {
                            $this->logger->log(
                                'notice',
                                'Template file ' . $file . ' is disabled by configuration'
                            );
                        }
                        continue;
                    }
                    $form->setOption(FormOption::TEMPLATE_FILE, $file->getPathname());
                    $form->setOption(FormOption::TEMPLATE_FILE_RELATIVE, substr($file->getRelativePathname(), 5, -5));
                    $form->setExtensionName($extensionName);
                    $output[$extensionName][$filename] = $form;
                } catch (InvalidSectionException | ChildNotFoundException $error) {
                    if ($this->logger instanceof LoggerInterface) {
                        $this->logger->log('error', $error->getMessage());
                    }
                }
            }
        }
        $this->runtimeCache->set($cacheKey, $output);
        return $output;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createTemplatePaths(string $registeredExtensionKey): TemplatePaths
    {
        /** @var TemplatePaths $templatePaths */
        $templatePaths = GeneralUtility::makeInstance(
            TemplatePaths::class,
            ExtensionNamingUtility::getExtensionKey($registeredExtensionKey)
        );
        return $templatePaths;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createViewInstance(
        string $extensionName,
        TemplatePaths $templatePaths,
        \SplFileInfo $file
    ): ViewInterface {
        /** @var TemplateView $view */
        $view = GeneralUtility::makeInstance(TemplateView::class);
        $view->getRenderingContext()->setTemplatePaths($templatePaths);
        $view->getRenderingContext()->getViewHelperVariableContainer()->addOrUpdate(
            FormViewHelper::SCOPE,
            FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME,
            $extensionName
        );
        $templatePaths->setTemplatePathAndFilename($file->getPathname());
        return $view;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getRootLine(int $pageUid): array
    {
        /** @var RootlineUtility $rootLineUtility */
        $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
        return $rootLineUtility->get();
    }
}
