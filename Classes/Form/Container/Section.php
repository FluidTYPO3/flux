<?php
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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Section
 */
class Section extends AbstractFormContainer implements ContainerInterface
{

    /**
     * @param array $settings
     * @return Section
     */
    public static function create(array $settings = [])
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var Section */
        $section = $objectManager->get(Section::class);
        foreach ($settings as $settingName => $settingValue) {
            $setterMethodName = ObjectAccess::buildSetterMethodName($settingName);
            if (true === method_exists($section, $setterMethodName)) {
                ObjectAccess::setProperty($section, $settingName, $settingValue);
            }
        }
        if (true === isset($settings['objects'])) {
            foreach ($settings['objects'] as $fieldName => $objectSettings) {
                if (false === isset($objectSettings['name'])) {
                    $objectSettings['name'] = $fieldName;
                }
                $object = Object::create($objectSettings);
                $section->add($object);
            }
        }
        return $section;
    }

    /**
     * @return array
     */
    public function build()
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
