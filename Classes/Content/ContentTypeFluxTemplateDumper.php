<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Form\Conversion\FormToFluidTemplateConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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

        $definition = $this->getContentType($parameters['row']['content_type']);
        if (!$definition) {
            return '';
        }
        $form = $definition->getForm();
        $grid = $definition->getGrid();
        $options = [
            FormToFluidTemplateConverter::OPTION_TEMPLATE_SOURCE => $definition->getTemplateSource()
        ];
        $dump = GeneralUtility::makeInstance(FormToFluidTemplateConverter::class)->convertFormAndGrid($form, $grid, $options);
        $parser = GeneralUtility::makeInstance(ObjectManager::class)->get(TemplateView::class)->getRenderingContext()->getTemplateParser();
        try {
            if (class_exists(Sequencer::class)) {
                $parser->parse(new Source($dump));
            } else {
                $parser->parse($dump);
            }
            $validation = '<p class="text-success">Template parses OK, it is safe to copy</p>';
        } catch (\Exception $error) {
            $validation = '<p class="text-danger">' . $error->getMessage() . '</p>';
        }
        $content = $validation . '<pre>' . htmlspecialchars($dump) . '</pre>';
        return $content;
    }

    protected function getContentType(string $contentTypeName): ?ContentTypeDefinitionInterface
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(ContentTypeManager::class)->determineContentTypeForTypeString($contentTypeName);
    }
}
