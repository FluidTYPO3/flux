<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Fluidbackend project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * ## Main form rendering ViewHelper
 *
 * Use to render a Flux form as HTML.
 */
class RenderViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('form', Form::class, 'Form instance to render as HTML', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        /** @var Form $form */
        $form = $arguments['form'];
        /** @var array $record */
        $record = $form->getOption(FormOption::RECORD);
        /** @var string $table */
        $table = $form->getOption(FormOption::RECORD_TABLE);
        /** @var string $field */
        $field = $form->getOption(FormOption::RECORD_FIELD);
        $node = static::getNodeFactory()->create([
            'type' => 'flex',
            'renderType' => 'flex',
            'flexFormDataStructureArray' => $form->build(),
            'tableName' => $table,
            'fieldName' => $field,
            'databaseRow' => $record,
            'inlineStructure' => [],
            'parameterArray' => [
                'itemFormElName' => sprintf('data[%s][%d][%s]', $table, (integer) $record['uid'], $field),
                'itemFormElValue' => static::convertXmlToArray($record[$field]),
                'fieldChangeFunc' => [],
                'fieldConf' => [
                    'config' => [
                        'ds' => $form->build(),
                    ],
                ],
            ],
        ]);
        $output = $node->render();
        return $output['html'] ?? '';
    }

    /**
     * @codeCoverageIgnore
     */
    protected static function convertXmlToArray(string $xml): array
    {
        $array = GeneralUtility::xml2array($xml);
        if (is_array($array)) {
            return $array;
        }
        return [];
    }

    /**
     * @codeCoverageIgnore
     */
    protected static function getNodeFactory(): NodeFactory
    {
        /** @var NodeFactory $nodeFactory */
        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);
        return $nodeFactory;
    }
}
