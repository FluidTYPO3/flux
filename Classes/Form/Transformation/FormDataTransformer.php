<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Hooks\HookHandler;
use TYPO3\CMS\Core\Service\FlexFormService;

class FormDataTransformer
{
    private FlexFormService $flexFormService;
    private DataTransformerRegistry $registry;

    public function __construct(FlexFormService $flexFormService, DataTransformerRegistry $registry)
    {
        $this->flexFormService = $flexFormService;
        $this->registry = $registry;
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
        if (null !== $form && $form->getOption(FormOption::TRANSFORM)) {
            $settings = $this->transformAccordingToConfiguration($settings, $form);
        }
        return $settings;
    }

    /**
     * Transforms members on $values recursively according to the provided
     * Flux configuration extracted from a Flux template. Uses "transform"
     * attributes on fields to determine how to transform values.
     */
    public function transformAccordingToConfiguration(array $values, Form $form, string $prefix = ''): array
    {
        foreach ($values as $index => $value) {
            if (is_array($value)) {
                $value = $this->transformAccordingToConfiguration($value, $form, $prefix . $index . '.');
                if ($object = $this->extractTransformableObjectByPath($form, $index)) {
                    $value = $this->transform($form, $index, $value);
                }
            } else {
                $value = $this->transform($form, $prefix . $index, $value);
            }
            $values[$index] = $value;
        }
        return $values;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function transform(Form $form, string $path, $value)
    {
        /** @var FieldInterface|ContainerInterface $object */
        $object = $this->extractTransformableObjectByPath($form, $path);
        if (is_object($object)) {
            $transformType = $object->getTransform();

            if ($transformType) {
                $originalValue = $value;
                $value = HookHandler::trigger(
                    HookHandler::VALUE_BEFORE_TRANSFORM,
                    [
                        'value' => $value,
                        'object' => $object,
                        'type' => $transformType,
                        'form' => $form
                    ]
                )['value'];
                if ($value === $originalValue) {
                    $value = $this->registry->resolveDataTransformerByType($transformType)
                        ->transform($object, $transformType, $value);
                }
                $value = HookHandler::trigger(
                    HookHandler::VALUE_AFTER_TRANSFORM,
                    [
                        'value' => $value,
                        'object' => $object,
                        'type' => $transformType,
                        'form' => $form
                    ]
                )['value'];
            }
        }
        return $value;
    }

    /**
     * @return mixed
     */
    protected function extractTransformableObjectByPath(ContainerInterface $subject, string $path)
    {
        $pathAsArray = explode('.', $path);
        $subPath = array_shift($pathAsArray);
        $child = null;
        while (count($pathAsArray)) {
            $child = $subject->get($subPath, $subject instanceof Form);
            if ($child) {
                if ($child instanceof Form\Container\Section) {
                    array_shift($pathAsArray);
                }
                if ($child instanceof ContainerInterface && count($pathAsArray)) {
                    return $this->extractTransformableObjectByPath($child, implode('.', $pathAsArray));
                }
            }
            $subPath .= '.' . array_shift($pathAsArray);
        }
        return $subject->get($path, true);
    }
}
