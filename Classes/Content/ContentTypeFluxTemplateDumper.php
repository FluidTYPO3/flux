<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form\Conversion\FormToFluidTemplateConverter;
use FluidTYPO3\Flux\Service\TemplateValidationService;
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
    private FormToFluidTemplateConverter $converter;
    private ContentTypeManager $contentTypeManager;
    private TemplateValidationService $validationService;

    public function __construct(
        FormToFluidTemplateConverter $converter,
        ContentTypeManager $contentTypeManager,
        TemplateValidationService $validationService
    ) {
        $this->converter = $converter;
        $this->contentTypeManager = $contentTypeManager;
        $this->validationService = $validationService;
    }

    public function dumpFluxTemplate(array $parameters): string
    {
        $record = $parameters['row'];
        $recordIsNew = strncmp((string)$record['uid'], 'NEW', 3) === 0;
        if ($recordIsNew) {
            return '';
        }

        /** @var RecordBasedContentTypeDefinition $definition */
        $definition = $this->contentTypeManager->determineContentTypeForTypeString($parameters['row']['content_type']);
        if (!$definition) {
            return '';
        }
        $form = $definition->getForm();
        $grid = $definition->getGrid();
        $options = [
            FormToFluidTemplateConverter::OPTION_TEMPLATE_SOURCE => $definition->getTemplateSource()
        ];

        $dump = $this->converter->convertFormAndGrid($form, $grid, $options);

        $error = $this->validationService->validateTemplateSource($dump);
        if ($error === null) {
            $validation = '<p class="text-success">Template parses OK, it is safe to copy</p>';
        } else {
            $validation = '<p class="text-danger">' . $error . '</p>';
        }

        $content = $validation . '<pre>' . htmlspecialchars($dump) . '</pre>';
        return $content;
    }
}
