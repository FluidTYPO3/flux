<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form\Conversion\FormToFluidTemplateConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Parser\Sequencer;
use TYPO3Fluid\Fluid\Core\Parser\Source;

/**
 * Content Template Dumper
 *
 * Dumps a content type definition as "legacy" Flux template
 * with all metadata embedded.
 */
class ContentTypeFluxTemplateDumper
{
    public function dumpFluxTemplate(array $parameters): string
    {
        $record = $parameters['row'];
        $recordIsNew = strncmp((string)$record['uid'], 'NEW', 3) === 0;
        if ($recordIsNew) {
            return '';
        }

        /** @var RecordBasedContentTypeDefinition|null $definition */
        $definition = $this->getContentType($parameters['row']['content_type']);
        if (!$definition) {
            return '';
        }
        $form = $definition->getForm();
        $grid = $definition->getGrid();
        $options = [
            FormToFluidTemplateConverter::OPTION_TEMPLATE_SOURCE => $definition->getTemplateSource()
        ];
        /** @var FormToFluidTemplateConverter $dumper */
        $dumper = GeneralUtility::makeInstance(FormToFluidTemplateConverter::class);
        $dump = $dumper->convertFormAndGrid($form, $grid, $options);

        $error = $this->validateTemplateSource($dump);
        if ($error === null) {
            $validation = '<p class="text-success">Template parses OK, it is safe to copy</p>';
        } else {
            $validation = '<p class="text-danger">' . $error->getMessage() . '</p>';
        }

        $content = $validation . '<pre>' . htmlspecialchars($dump) . '</pre>';
        return $content;
    }

    protected function validateTemplateSource(string $templateSource): ?\Exception
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var TemplateView $templateView */
        $templateView = $objectManager->get(TemplateView::class);
        $parser = $templateView->getRenderingContext()->getTemplateParser();
        try {
            if (class_exists(Sequencer::class)) {
                $parser->parse(new Source($templateSource));
            } else {
                $parser->parse($templateSource);
            }
        } catch (\Exception $error) {
            return $error;
        }
        return null;
    }

    protected function getContentType(string $contentTypeName): ?ContentTypeDefinitionInterface
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var ContentTypeManager $contentTypeManager */
        $contentTypeManager = $objectManager->get(ContentTypeManager::class);
        return $contentTypeManager->determineContentTypeForTypeString($contentTypeName);
    }
}
