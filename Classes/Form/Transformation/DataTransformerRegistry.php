<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ServiceLocator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataTransformerRegistry
{
    /**
     * @var DataTransformerInterface[]
     */
    private array $transformers = [];

    private static array $legacy = [];

    public function __construct(ServiceLocator $locator)
    {
        /** @var DataTransformerInterface[] $transformers */
        $transformers = array_map([$locator, 'get'], array_keys($locator->getProvidedServices()));
        $this->transformers = $transformers;
        usort(
            $this->transformers,
            function (DataTransformerInterface $a, DataTransformerInterface $b) {
                return $b->getPriority() <=> $a->getPriority();
            }
        );
    }

    public static function registerTransformerOnLegacyPhpVersion(string $transformerClassName): void
    {
        self::$legacy[] = $transformerClassName;
    }

    public function resolveDataTransformerByType(string $type): DataTransformerInterface
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransformToType($type)) {
                return $transformer;
            }
        }

        /** @var class-string $legacyClassName */
        foreach (self::$legacy as $legacyClassName) {
            /** @var DataTransformerInterface $transformer */
            $transformer = GeneralUtility::makeInstance($legacyClassName);
            if ($transformer->canTransformToType($type)) {
                return $transformer;
            }
        }

        throw new \InvalidArgumentException(
            'Flux could not resolve a data transformer for type "' . $type . '"',
            1720346755
        );
    }
}
