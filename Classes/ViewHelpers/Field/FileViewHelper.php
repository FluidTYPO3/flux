<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\File;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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
 *
 * DEPRECATED - use flux:field instead
 * @deprecated Will be removed in Flux 10.0
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
        $this->registerArgument(
            'internalType',
            'string',
            'Internal type (TCA internal_type) to use for the field. Defaults to `file_reference` but can be set to ' .
            '`file` to support file uploading',
            false,
            'file_reference'
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @return File
     */
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments)
    {
        /** @var File $component */
        $component = static::getPreparedComponent('File', $renderingContext, $arguments);
        $component->setMaxSize($arguments['maxSize']);
        $component->setDisallowed($arguments['disallowed']);
        $component->setAllowed($arguments['allowed']);
        $component->setUploadFolder($arguments['uploadFolder']);
        $component->setShowThumbnails($arguments['showThumbnails']);
        $component->setUseFalRelation($arguments['useFalRelation']);
        $component->setInternalType($arguments['internalType']);
        return $component;
    }
}
