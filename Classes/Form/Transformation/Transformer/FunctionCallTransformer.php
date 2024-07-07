<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Attribute\DataTransformer;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\Transformation\DataTransformerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Function Call Transformer
 *
 * @deprecated Use a custom DataTransformer instead.
 */
#[DataTransformer('flux.datatransformer.function')]
class FunctionCallTransformer implements DataTransformerInterface
{
    public function canTransformToType(string $type): bool
    {
        return strpos($type, '->') !== false;
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform(FormInterface $component, string $type, $value)
    {
        /** @var class-string $class */
        [$class, $function] = explode('->', $type);
        /** @var object $object */
        $object = GeneralUtility::makeInstance($class);
        return $object->{$function}($value, $component->getName(), $component->getRoot());
    }
}
