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
 * Array Transformer
 */
#[DataTransformer('flux.datatransformer.array')]
class ArrayTransformer implements DataTransformerInterface
{
    public function canTransformToType(string $type): bool
    {
        return $type === 'array';
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @param string|array $value
     * @return array|null
     */
    public function transform(FormInterface $component, string $type, $value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            return GeneralUtility::trimExplode(',', $value, true);
        }
        if (is_iterable($value)) {
            return iterator_to_array($value);
        }
        return (array) $value;
    }
}
