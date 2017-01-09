<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\File;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Group (select supertype) FlexForm field ViewHelper, subtype "file"
 *
 * ### Select and render an image
 *
 *     <flux:field.file name="settings.image" allowed="jpg,png,svg" showThumbnails="1" />
 *
 * Then use `<f:image>` to render the image in the frontend:
 *
 *     <f:image src="{settings.image}"/>
 *
 * `alt` and `title` tags are not loaded from the file's meta data record.
 * Use `<flux:field.inline.fal>` if you want this feature.
 */
class FileViewHelper extends AbstractMultiValueFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('maxSize', 'integer', 'Maximum file size allowed in KB');
        $this->registerArgument('allowed', 'string', 'Defines a list of file types allowed in this field');
        $this->registerArgument('disallowed', 'string', 'Defines a list of file types NOT allowed in this field');
        $this->registerArgument(
            'uploadFolder',
            'string',
            'Upload folder to use for copied/directly uploaded files'
        );
        $this->registerArgument(
            'showThumbnails',
            'boolean',
            'If TRUE, displays thumbnails for selected values',
            false,
            false
        );
        $this->registerArgument(
            'useFalRelation',
            'boolean',
            'use a fal relation instead of a simple file path',
            false,
            false
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return File
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var File $component */
        $component = static::getPreparedComponent('File', $renderingContext, $arguments);
        $component->setMaxSize($arguments['maxSize']);
        $component->setDisallowed($arguments['disallowed']);
        $component->setAllowed($arguments['allowed']);
        $component->setUploadFolder($arguments['uploadFolder']);
        $component->setShowThumbnails($arguments['showThumbnails']);
        $component->setUseFalRelation($arguments['useFalRelation']);
        return $component;
    }
}
