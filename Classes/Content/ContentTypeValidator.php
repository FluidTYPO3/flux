<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Parser\Sequencer;
use TYPO3Fluid\Fluid\Core\Parser\Source;

/**
 * TCA user feedback: Content Type Validation
 *
 * Provides feedback with validation of the content
 * type being edited, such as validating the Fluid
 * template, the CType value, extension context and
 * other data.
 *
 * Rendered as a custom TCA field.
 */
class ContentTypeValidator
{
    public function validateContentTypeRecord(array $parameters): string
    {
        static $content = '';
        if (empty($content)) {
            /** @var ObjectManagerInterface $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var Request $request */
            $request = $objectManager->get(Request::class);
            /** @var ControllerContext $context */
            $context = $objectManager->get(ControllerContext::class);
            $context->setRequest($request);
            /** @var TemplateView $view */
            $view = $objectManager->get(TemplateView::class);
            $view->getRenderingContext()->setControllerContext($context);
            $view->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('flux');
            $view->getRenderingContext()->setControllerName('Content');

            $record = $parameters['row'];
            $recordIsNew = strncmp((string)$record['uid'], 'NEW', 3) === 0;

            $view->assign('recordIsNew', $recordIsNew);

            if ($recordIsNew) {
                return $view->render('validation');
            }

            /** @var ContentTypeManager $contentTypeManager */
            $contentTypeManager = $objectManager->get(ContentTypeManager::class);
            $contentType = $contentTypeManager->determineContentTypeForTypeString($record['content_type']);
            if (!$contentType) {
                $view->assign('recordIsNew', true);
                return $view->render('validation');
            }

            $usesTemplateFile = true;
            if ($contentType instanceof FluidRenderingContentTypeDefinitionInterface) {
                $usesTemplateFile = $contentType->isUsingTemplateFile()
                    && file_exists(GeneralUtility::getFileAbsFileName($contentType->getTemplatePathAndFilename()));
            }

            $view->assignMultiple([
                'recordIsNew' => $recordIsNew,
                'contentType' => $contentType,
                'record' => $record,
                'usages' => $this->countUsages($contentType),
                'validation' => [
                    'extensionInstalled' => $this->validateContextExtensionIsInstalled($contentType),
                    'extensionMatched' => $this->validateContextMatchesSignature($contentType),
                    'templateSource' => $this->validateTemplateSource($contentType),
                    'templateFile' => $usesTemplateFile,
                    'icon' => !empty($record['icon'])
                        && file_exists(GeneralUtility::getFileAbsFileName($record['icon'])),
                ],
            ]);

            return $view->render('validation');
        }
        return $content;
    }

    protected function validateTemplateSource(ContentTypeDefinitionInterface $definition): ?string
    {
        if (!$definition instanceof RecordBasedContentTypeDefinition) {
            return null;
        }
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var TemplateView $templateView */
        $templateView = $objectManager->get(TemplateView::class);
        $source = $definition->getTemplatesource();
        $parser = $templateView->getRenderingContext()->getTemplateParser();
        try {
            if (class_exists(Sequencer::class)) {
                $parser->parse(new Source($source));
            } else {
                $parser->parse($source);
            }
        } catch (\Exception $error) {
            return $error->getMessage();
        }
        return null;
    }

    protected function validateContextMatchesSignature(ContentTypeDefinitionInterface $definition): bool
    {
        $parts = explode('_', $definition->getContentTypeName());
        return str_replace(
            '_',
            '',
            ExtensionNamingUtility::getExtensionKey($definition->getExtensionIdentity())
        ) === reset($parts);
    }

    protected function validateContextExtensionIsInstalled(ContentTypeDefinitionInterface $definition): bool
    {
        return ExtensionManagementUtility::isLoaded(
            ExtensionNamingUtility::getExtensionKey($definition->getExtensionIdentity())
        );
    }

    protected function countUsages(ContentTypeDefinitionInterface $definition): int
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter($definition->getContentTypeName(), \PDO::PARAM_STR)
                )
            );
        return (integer) $queryBuilder->execute()->rowCount();
    }
}
