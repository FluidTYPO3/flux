<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;

/**
 * Page Service
 *
 * Service for interacting with Pages - gets content elements and page configuration
 * options.
 */
class PageService implements SingletonInterface
{
    protected ConfigurationManagerInterface $configurationManager;
    protected FluxService $configurationService;
    protected WorkspacesAwareRecordService $workspacesAwareRecordService;

    public function __construct()
    {
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->configurationManager = $configurationManager;

        /** @var FluxService $configurationService */
        $configurationService = GeneralUtility::makeInstance(FluxService::class);
        $this->configurationService = $configurationService;

        /** @var WorkspacesAwareRecordService $recordService */
        $recordService = GeneralUtility::makeInstance(WorkspacesAwareRecordService::class);
        $this->workspacesAwareRecordService = $recordService;
    }

    /**
     * Process RootLine to find first usable, configured Fluid Page Template.
     * WARNING: do NOT use the output of this feature to overwrite $row - the
     * record returned may or may not be the same record as defined in $id.
     *
     * @param integer $pageUid
     * @return array|null
     * @api
     */
    public function getPageTemplateConfiguration($pageUid)
    {
        $pageUid = (integer) $pageUid;
        if (1 > $pageUid) {
            return null;
        }
        $cacheId = 'flux-template-configuration-' . $pageUid;
        $runtimeCache = $this->getRuntimeCache();
        /** @var array|null $fromCache */
        $fromCache = $runtimeCache->get($cacheId);
        if ($fromCache) {
            return $fromCache;
        }


        $resolvedMainTemplateIdentity = null;
        $resolvedSubTemplateIdentity = null;
        $rootLineUtility = $this->getRootLineUtility($pageUid);

        // Initialize with possibly-empty values and loop root line
        // to fill values as they are detected.
        foreach ($rootLineUtility->get() as $page) {
            $resolvedMainTemplateIdentity = is_array($page['tx_fed_page_controller_action'] ?? null)
                ? $page['tx_fed_page_controller_action'][0]
                : $page['tx_fed_page_controller_action'] ?? null;
            $resolvedSubTemplateIdentity = is_array($page['tx_fed_page_controller_action_sub'] ?? null)
                ? $page['tx_fed_page_controller_action_sub'][0]
                : $page['tx_fed_page_controller_action_sub'] ?? null;
            $containsSubDefinition = (false !== strpos($page['tx_fed_page_controller_action_sub'] ?? '', '->'));
            $isCandidate = ((integer) ($page['uid'] ?? 0) !== $pageUid);
            if (true === $containsSubDefinition && true === $isCandidate) {
                $resolvedSubTemplateIdentity = $page['tx_fed_page_controller_action_sub'] ?? null;
                if (true === empty($resolvedMainTemplateIdentity)) {
                    // Conditions met: current page is not $pageUid, original page did not
                    // contain a "this page" layout, current rootline page has "sub" selection.
                    // Then, set our "this page" value to use the "sub" selection that was detected.
                    $resolvedMainTemplateIdentity = $resolvedSubTemplateIdentity;
                }
                break;
            }
        };
        if (true === empty($resolvedMainTemplateIdentity) && true === empty($resolvedSubTemplateIdentity)) {
            // Neither directly configured "this page" nor inherited "sub" contains a valid value;
            // no configuration was detected at all.
            return null;
        }
        $configuration = [
            'tx_fed_page_controller_action' => $resolvedMainTemplateIdentity,
            'tx_fed_page_controller_action_sub' => $resolvedSubTemplateIdentity
        ];
        $runtimeCache->set($cacheId, $configuration);
        return $configuration;
    }

    /**
     * Get a usable page configuration flexform from rootline
     *
     * @param integer $pageUid
     * @return string|null
     * @api
     */
    public function getPageFlexFormSource($pageUid)
    {
        $pageUid = (integer) $pageUid;
        if (1 > $pageUid) {
            return null;
        }
        $fieldList = 'uid,pid,t3ver_oid,tx_fed_page_flexform';
        $page = $this->workspacesAwareRecordService->getSingle('pages', $fieldList, $pageUid);
        while (null !== $page && 0 !== (integer) $page['uid'] && true === empty($page['tx_fed_page_flexform'])) {
            $resolveParentPageUid = (integer) (0 > $page['pid'] ? $page['t3ver_oid'] : $page['pid']);
            $page = $this->workspacesAwareRecordService->getSingle('pages', $fieldList, $resolveParentPageUid);
        }
        return $page['tx_fed_page_flexform'] ?? null;
    }

    /**
     * Gets a list of usable Page Templates from defined page template TypoScript.
     * Returns a list of Form instances indexed by the path ot the template file.
     *
     * @return Form[][]
     * @api
     */
    public function getAvailablePageTemplateFiles()
    {
        $cache = $this->getRuntimeCache();
        $cacheKey = 'page_templates';
        /** @var array|null $fromCache */
        $fromCache = $cache->get($cacheKey);
        if ($fromCache) {
            return $fromCache;
        }
        $typoScript = $this->configurationService->getPageConfiguration();
        $output = [];

        /** @var TemplateView $view */
        $view = GeneralUtility::makeInstance(TemplateView::class);
        foreach ((array) $typoScript as $extensionName => $group) {
            if (true === isset($group['enable']) && 1 > $group['enable']) {
                continue;
            }
            $output[$extensionName] = [];
            $templatePaths = $this->createTemplatePaths($extensionName);
            $finder = Finder::create()->in($templatePaths->getTemplateRootPaths())->name('*.html')->sortByName();
            foreach ($finder->files() as $file) {
                /** @var \SplFileInfo $file */
                if ('.' === substr($file->getBasename(), 0, 1)) {
                    continue;
                }
                if (strpos($file->getPath(), DIRECTORY_SEPARATOR . 'Page') === false) {
                    continue;
                }
                $filename = $file->getRelativePathname();
                if (isset($output[$extensionName][$filename])) {
                    continue;
                }

                $view->getRenderingContext()->setTemplatePaths($templatePaths);
                $view->getRenderingContext()->getViewHelperVariableContainer()->addOrUpdate(
                    FormViewHelper::SCOPE,
                    FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME,
                    $extensionName
                );
                $view->setTemplatePathAndFilename($file->getPathname());
                try {
                    $view->renderSection('Configuration');
                    $form = $view->getRenderingContext()
                        ->getViewHelperVariableContainer()
                        ->get(FormViewHelper::class, 'form');

                    if (false === $form instanceof Form) {
                        $this->getLogger()->log(
                            'error',
                            'Template file ' . $file . ' contains an unparsable Form definition'
                        );
                        continue;
                    } elseif (false === $form->getEnabled()) {
                        $this->getLogger()->log(
                            'notice',
                            'Template file ' . $file . ' is disabled by configuration'
                        );
                        continue;
                    }
                    $form->setOption(Form::OPTION_TEMPLATEFILE, $file->getPathname());
                    $form->setOption(Form::OPTION_TEMPLATEFILE_RELATIVE, substr($file->getRelativePathname(), 5, -5));
                    $form->setExtensionName($extensionName);
                    $output[$extensionName][$filename] = $form;
                } catch (InvalidSectionException $error) {
                    $this->getLogger()->log('error', $error->getMessage());
                } catch (ChildNotFoundException $error) {
                    $this->getLogger()->log('error', $error->getMessage());
                }
            }
        }
        $cache->set($cacheKey, $output);
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
    protected function getLogger(): LoggerInterface
    {
        /** @var LogManager $logManager */
        $logManager = GeneralUtility::makeInstance(LogManager::class);
        $logger = $logManager->getLogger(__CLASS__);
        return $logger;
    }

    /**
     * @codeCoverageIgnore
     * @return FrontendInterface
     */
    protected function getRuntimeCache()
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        return $cacheManager->getCache('runtime');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getRootLineUtility(int $pageUid): RootlineUtility
    {
        /** @var RootlineUtility $rootLineUtility */
        $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
        return $rootLineUtility;
    }
}
