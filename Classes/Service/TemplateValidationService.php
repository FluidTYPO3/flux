<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Parser\Sequencer;
use TYPO3Fluid\Fluid\Core\Parser\Source;

class TemplateValidationService implements SingletonInterface
{
    public function validateContentDefinition(ContentTypeDefinitionInterface $definition): ?string
    {
        if (!$definition instanceof RecordBasedContentTypeDefinition) {
            return null;
        }
        return $this->validateTemplateSource($definition->getTemplatesource());
    }

    public function validateTemplateSource(string $source): ?string
    {
        /** @var TemplateView $templateView */
        $templateView = GeneralUtility::makeInstance(TemplateView::class);
        $renderingContext = $templateView->getRenderingContext();
        $renderingContext->getTemplatePaths()->fillDefaultsByPackageName('flux');
        $parser = $renderingContext->getTemplateParser();
        try {
            $parser->parse(class_exists(Sequencer::class) ? new Source($source) : $source);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
        return null;
    }
}
