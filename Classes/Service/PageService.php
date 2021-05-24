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
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var WorkspacesAwareRecordService
     */
    protected $workspacesAwareRecordService;

    /**
     * @param ObjectManager $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @param WorkspacesAwareRecordService $workspacesAwareRecordService
     * @return void
     */
    public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService)
    {
        $this->workspacesAwareRecordService = $workspacesAwareRecordService;
    }

    /**
     * Process RootLine to find first usable, configured Fluid Page Template.
     * WARNING: do NOT use the output of this feature to overwrite $row - the
     * record returned may or may not be the same record as defined in $id.
     *
     * @param integer $pageUid
     * @return array|NULL
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
        $fromCache = $runtimeCache->get($cacheId);
        if ($fromCache) {
            return $fromCache;
        }
        $fieldList = 'tx_fed_page_controller_action_sub,t3ver_oid,pid,uid';
        $page = $this->workspacesAwareRecordService->getSingle(
            'pages',
            'tx_fed_page_controller_action,' . $fieldList,
            $pageUid
        );
        if (null === $page) {
            return null;
        }

        // Initialize with possibly-empty values and loop root line
        // to fill values as they are detected.
        do {
            $resolvedMainTemplateIdentity = is_array($page['tx_fed_page_controller_action']) ? $page['tx_fed_page_controller_action'][0] : $page['tx_fed_page_controller_action'];
            $resolvedSubTemplateIdentity = is_array($page['tx_fed_page_controller_action_sub']) ? $page['tx_fed_page_controller_action_sub'][0] : $page['tx_fed_page_controller_action_sub'];
            $containsSubDefinition = (false !== strpos($page['tx_fed_page_controller_action_sub'], '->'));
            $isCandidate = ((integer) $page['uid'] !== $pageUid);
            if (true === $containsSubDefinition && true === $isCandidate) {
                $resolvedSubTemplateIdentity = $page['tx_fed_page_controller_action_sub'];
                if (true === empty($resolvedMainTemplateIdentity)) {
                    // Conditions met: current page is not $pageUid, original page did not
                    // contain a "this page" layout, current rootline page has "sub" selection.
                    // Then, set our "this page" value to use the "sub" selection that was detected.
                    $resolvedMainTemplateIdentity = $resolvedSubTemplateIdentity;
                }
                break;
            }
            // Note: 't3ver_oid' is analysed in order to make versioned records inherit the original record's
            // configuration as an emulated first parent page.
            $resolveParentPageUid = $page['pid'];
            // Avoid useless SQL query if uid is 0, because uids in the database start from 1.
            if (0 === $resolveParentPageUid) {
                break;
            }
            $page = $this->workspacesAwareRecordService->getSingle(
                'pages',
                $fieldList,
                $resolveParentPageUid
            );
        } while (null !== $page);
        if (true === empty($resolvedMainTemplateIdentity) && true === empty($resolvedSubTemplateIdentity)) {
            // Neither directly configured "this page" nor inherited "sub" contains a valid value;
            // no configuration was detected at all.
            return null;
        }
        $configurarion = [
            'tx_fed_page_controller_action' => $resolvedMainTemplateIdentity,
            'tx_fed_page_controller_action_sub' => $resolvedSubTemplateIdentity
        ];
        $runtimeCache->set($cacheId, $configurarion);
        return $configurarion;
    }

    /**
     * Get a usable page configuration flexform from rootline
     *
     * @param integer $pageUid
     * @return string
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
        return $page['tx_fed_page_flexform'];
    }

    /**
     * Gets a list of usable Page Templates from defined page template TypoScript.
     * Returns a list of Form instances indexed by the path ot the template file.
     *
     * @return Form[]
     * @api
     */
    public function getAvailablePageTemplateFiles()
    {
        $cache = $this->getRuntimeCache();
        $cacheKey = 'page_templates';
        $fromCache = $cache->get($cacheKey);
        if ($fromCache) {
            return $fromCache;
        }
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $typoScript = $this->configurationService->getPageConfiguration();
        $output = [];
        foreach ((array) $typoScript as $extensionName => $group) {
            if (true === isset($group['enable']) && 1 > $group['enable']) {
                continue;
            }
            $output[$extensionName] = [];
            $templatePaths = GeneralUtility::makeInstance(TemplatePaths::class, ExtensionNamingUtility::getExtensionKey($extensionName));
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

                $view = $this->objectManager->get(TemplateView::class);
                $view->getRenderingContext()->setTemplatePaths($templatePaths);
                $view->getRenderingContext()->getViewHelperVariableContainer()->addOrUpdate(FormViewHelper::SCOPE, FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME, $extensionName);
                $view->setTemplatePathAndFilename($file->getPathname());
                try {
                    $view->renderSection('Configuration');
                    $form = $view->getRenderingContext()->getViewHelperVariableContainer()->get(FormViewHelper::class, 'form');

                    if (false === $form instanceof Form) {
                        $logger->log(
                            'error',
                            'Template file ' . $file . ' contains an unparsable Form definition'
                        );
                        continue;
                    } elseif (false === $form->getEnabled()) {
                        $logger->log(
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
                    $logger->log('error', $error->getMessage());
                } catch (ChildNotFoundException $error) {
                    $logger->log('error', $error->getMessage());
                }
            }
        }
        $cache->set($cacheKey, $output);
        return $output;
    }

    /**
     * @return VariableFrontend
     */
    protected function getRuntimeCache()
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_runtime');
    }
}
