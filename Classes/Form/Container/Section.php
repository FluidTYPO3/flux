<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * TCEForms Section object representation
 *
 * Must be used as container around section objects. In TYPO3 these
 * become sections where the different types of objects you define,
 * as children inside the container.
 *
 * Section objects from Flux also combine the normal TYPO3 TCEForms
 * behavior with the ability to flag section objects as "content
 * containers" which means they automatically produce nested content
 * columns as a Grid. A flag on this object then determins if the
 * section objects that become grid containers, should be rendered
 * as rows or columns.
 */
class Section extends AbstractFormContainer implements ContainerInterface
{
    const GRID_MODE_ROWS = 'rows';
    const GRID_MODE_COLUMNS = 'columns';

    protected string $gridMode = self::GRID_MODE_ROWS;

    public static function create(array $settings = []): self
    {
        /** @var Section */
        $section = GeneralUtility::makeInstance(Section::class);
        foreach ($settings as $settingName => $settingValue) {
            $setterMethodName = 'set' . ucfirst($settingName);
            if (true === method_exists($section, $setterMethodName)) {
                ObjectAccess::setProperty($section, $settingName, $settingValue);
            }
        }
        if (true === isset($settings['objects'])) {
            foreach ($settings['objects'] as $fieldName => $objectSettings) {
                if (false === isset($objectSettings['name'])) {
                    $objectSettings['name'] = $fieldName;
                }
                $object = SectionObject::create($objectSettings);
                $section->add($object);
            }
        }
        return $section;
    }

    public function getGridMode(): string
    {
        return $this->gridMode;
    }

    public function setGridMode(string $gridMode): self
    {
        $this->gridMode = $gridMode;
        return $this;
    }

    public function getContentContainer(): ?SectionObject
    {
        foreach ($this->children as $child) {
            if ($child instanceof SectionObject && $child->isContentContainer()) {
                return $child;
            }
        }
        return null;
    }

    public function build(): array
    {
        $structureArray = [
            'type' => 'array',
            'title' => $this->getLabel(),
            'section' => '1',
            'el' => $this->buildChildren($this->children)
        ];
        return $structureArray;
    }
}
