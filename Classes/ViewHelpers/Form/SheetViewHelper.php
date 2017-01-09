<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Sheet;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * FlexForm sheet ViewHelper
 *
 * Groups FlexForm fields into sheets.
 */
class SheetViewHelper extends AbstractFormViewHelper
{

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'name',
            'string',
            'Name of the group, used as FlexForm sheet name, must be FlexForm XML-valid tag name string',
            true
        );
        $this->registerArgument(
            'label',
            'string',
            'Label for the field group - used as tab name in FlexForm. Optional - if not specified, Flux tries to ' .
            'detect an LLL label named "flux.fluxFormId.sheets.foobar" based on sheet name, in scope of extension ' .
            'rendering the Flux form.'
        );
        $this->registerArgument(
            'variables',
            'array',
            'Freestyle variables which become assigned to the resulting Component - can then be read from that ' .
            'Component outside this Fluid template and in other templates using the Form object from this template',
            false,
            []
        );
        $this->registerArgument(
            'description',
            'string',
            'Optional string or LLL reference with a desription of the purpose of the sheet'
        );
        $this->registerArgument(
            'shortDescription',
            'string',
            'Optional shorter version of description of purpose of the sheet, LLL reference supported'
        );
        $this->registerArgument(
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension ' .
            'name is otherwise automatically detected from rendering context.'
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Sheet
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        $form = static::getFormFromRenderingContext($renderingContext);
        $extensionName = static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments);
        if (true === $form->has($arguments['name'])) {
            $sheet = $form->get($arguments['name']);
        } else {
            /** @var Sheet $sheet */
            $sheet = $form->createContainer('Sheet', $arguments['name'], $arguments['label']);
        }
        $sheet->setExtensionName($extensionName);
        $sheet->setVariables($arguments['variables']);
        $sheet->setDescription($arguments['description']);
        $sheet->setShortDescription($arguments['shortDescription']);
        return $sheet;
    }
}
