<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Text;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Textarea FlexForm field ViewHelper
 */
class TextViewHelper extends AbstractFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'validate',
            'string',
            'FlexForm-type validation configuration for this input',
            false,
            'trim'
        );
        $this->registerArgument('cols', 'int', 'Number of columns in editor', false, 85);
        $this->registerArgument('rows', 'int', 'Number of rows in editor', false, 10);
        $this->registerArgument(
            'defaultExtras',
            'string',
            'FlexForm-syntax "defaultExtras" definition, example: "richtext[*]:rte_transform[mode=ts_css]"',
            false,
            ''
        );
        $this->registerArgument(
            'enableRichText',
            'boolean',
            'Shortcut for adding value of TS plugin.tx_flux.settings.flexform.rteDefaults to "defaultExtras"',
            false,
            false
        );
        $this->registerArgument(
            'renderType',
            'string',
            'Render type allows you to modify the behaviour of text field. At the moment only t3editor and none ' .
            '(works as disabled) are supported but you can create your own. More information: ' .
            'https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Text/Index.html#rendertype',
            false,
            ''
        );
        $this->registerArgument(
            'format',
            'string',
            'Format is used with renderType and, at the moment, is just useful if renderType is equals to t3editor. ' .
            'At the moment possible values are:  html, typoscript, javascript, css, xml, html, php, sparql, mixed. ' .
            'More information: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Text/Index.html#format',
            false,
            ''
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Text
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var Text $text */
        $text = static::getPreparedComponent('Text', $renderingContext, $arguments);
        $text->setValidate($arguments['validate']);
        $text->setColumns($arguments['cols']);
        $text->setRows($arguments['rows']);
        $text->setDefaultExtras($arguments['defaultExtras']);
        $text->setEnableRichText($arguments['enableRichText']);
        $text->setRenderType($arguments['renderType']);
        $text->setFormat($arguments['format']);
        return $text;
    }
}
