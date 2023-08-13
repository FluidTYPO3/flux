<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Text;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Textarea FlexForm field ViewHelper
 */
class TextViewHelper extends AbstractFieldViewHelper
{
    public function initializeArguments(): void
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
            'DEPRECATED, IGNORED - has no function on TYPO3 8.7+. FlexForm-syntax "defaultExtras" definition, '
            . 'example: "richtext[*]:rte_transform[mode=ts_css]"',
            false,
            ''
        );
        $this->registerArgument(
            'enableRichText',
            'boolean',
            'Enable the richtext editor (RTE)',
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
        $this->registerArgument(
            'richtextConfiguration',
            'string',
            'Specifies which configuration to use in combination with EXT:rte_ckeditor.' .
            'If none is given, PageTSconfig "RTE.tx_flux.preset" and "RTE.default.preset" are used.' .
            'More information: '
            . 'https://docs.typo3.org/typo3cms/TCAReference/ColumnsConfig/Properties/TextRichtextConfiugration.html'
        );
        $this->registerArgument(
            'placeholder',
            'string',
            'Placeholder text which vanishes if field is filled and/or field is focused'
        );
    }

    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Text
    {
        /** @var array $arguments */
        /** @var Text $text */
        $text = static::getPreparedComponent(Text::class, $renderingContext, $arguments);
        $text->setValidate($arguments['validate']);
        $text->setColumns($arguments['cols']);
        $text->setRows($arguments['rows']);
        $text->setEnableRichText($arguments['enableRichText']);
        $text->setRichtextConfiguration($arguments['richtextConfiguration'] ?? '');
        $text->setRenderType($arguments['renderType']);
        $text->setFormat($arguments['format']);
        $text->setPlaceholder($arguments['placeholder']);
        return $text;
    }
}
