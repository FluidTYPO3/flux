<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 */
class FluxService implements SingletonInterface
{
    protected FormDataTransformer $transformer;
    protected FlexFormService $flexFormService;

    public function __construct(
        FormDataTransformer $transformer,
        FlexFormService $flexFormService
    ) {
        $this->transformer = $transformer;
        $this->flexFormService = $flexFormService;
    }

    /**
     * Parses the flexForm content and converts it to an array
     * The resulting array will be multi-dimensional, as a value "bla.blubb"
     * results in two levels, and a value "bla.blubb.bla" results in three levels.
     *
     * Note: multi-language flexForms are not supported yet
     *
     * @param string $flexFormContent flexForm xml string
     * @param Form $form An instance of \FluidTYPO3\Flux\Form. If transformation instructions are contained in this
     *                   configuration they are applied after conversion to array
     * @param string|null $languagePointer language pointer used in the flexForm
     * @param string|null $valuePointer value pointer used in the flexForm
     */
    public function convertFlexFormContentToArray(
        string $flexFormContent,
        Form $form = null,
        ?string $languagePointer = 'lDEF',
        ?string $valuePointer = 'vDEF'
    ): array {
        if (true === empty($flexFormContent)) {
            return [];
        }
        if (true === empty($languagePointer)) {
            $languagePointer = 'lDEF';
        }
        if (true === empty($valuePointer)) {
            $valuePointer = 'vDEF';
        }
        $settings = $this->flexFormService->convertFlexFormContentToArray(
            $flexFormContent,
            $languagePointer,
            $valuePointer
        );
        if (null !== $form && $form->getOption(Form::OPTION_TRANSFORM)) {
            $settings = $this->transformer->transformAccordingToConfiguration($settings, $form);
        }
        return $settings;
    }
}
