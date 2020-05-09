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
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $request = $objectManager->get(Request::class);
            $context = $objectManager->get(ControllerContext::class);
            $context->setRequest($request);
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

            $contentType = $objectManager->get(ContentTypeManager::class)->determineContentTypeForTypeString($record['content_type']);
            if (!$contentType) {
                $view->assign('recordIsNew', true);
                return $view->render('validation');
            }

            $usesTemplateFile = true;
            if ($contentType instanceof FluidRenderingContentTypeDefinitionInterface) {
                $usesTemplateFile = $contentType->isUsingTemplateFile() ? file_exists(GeneralUtility::getFileAbsFileName($contentType->getTemplatePathAndFilename())) : false;
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
                    'icon' => !empty($record['icon']) ? file_exists(GeneralUtility::getFileAbsFileName($record['icon'])) : true,
                ],
            ]);

            return $view->render('validation');
        }
        return $content;
    }

    protected function validateTemplateSource(ContentTypeDefinitionInterface $definition): ?string
    {
        $parser = GeneralUtility::makeInstance(ObjectManager::class)->get(TemplateView::class)->getRenderingContext()->getTemplateParser();
        try {
            if (class_exists(Sequencer::class)) {
                $parser->parse(new Source($definition->getTemplatesource()));
            } else {
                $parser->parse($definition->getTemplateSource());
            }
        } catch (\Exception $error) {
            return $error->getMessage();
        }
        return null;
    }

    protected function validateContextMatchesSignature(ContentTypeDefinitionInterface $definition): bool
    {
        return str_replace('_', '', ExtensionNamingUtility::getExtensionKey($definition->getExtensionIdentity())) === reset(explode('_', $definition->getContentTypeName()));
    }

    protected function validateContextExtensionIsInstalled(ContentTypeDefinitionInterface $definition): bool
    {
        return ExtensionManagementUtility::isLoaded(ExtensionNamingUtility::getExtensionKey($definition->getExtensionIdentity()));
    }

    protected function countUsages(ContentTypeDefinitionInterface $definition): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter($definition->getContentTypeName(), \PDO::PARAM_STR)
                )
            );
        return $queryBuilder->execute()->rowCount();
    }
}
