<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\MultiValueFieldInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for all FlexForm fields.
 */
abstract class AbstractMultiValueFieldViewHelper extends AbstractFieldViewHelper
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
        $this->registerArgument('size', 'integer', 'Size of the selector box', false, 1);
        $this->registerArgument('multiple', 'boolean', 'If TRUE, allows multiple selections', false, false);
        $this->registerArgument('minItems', 'integer', 'Minimum required number of items to be selected', false, 0);
        $this->registerArgument('maxItems', 'integer', 'Maxium allowed number of items to be selected', false, 1);
        $this->registerArgument('itemListStyle', 'string', 'Overrides the default list style when maxItems > 1');
        $this->registerArgument(
            'selectedListStyle',
            'string',
            'Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle'
        );
        $this->registerArgument(
            'items',
            'mixed',
            'Items for the selector; array / CSV / Traversable / Query supported'
        );
        $this->registerArgument(
            'emptyOption',
            'mixed',
            'If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this ' .
            'property\'s value (cast to string) as label.',
            false,
            false
        );
        $this->registerArgument(
            'translateCsvItems',
            'boolean',
            'If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using ' .
            'convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined ' .
            'by normal Flux field name conventions'
        );
        $this->registerArgument(
            'itemsProcFunc',
            'string',
            'Function for serving items. See TCA "select" field "itemsProcFunc" attribute'
        );
    }

    /**
     * @param string $type
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return MultiValueFieldInterface
     */
    protected static function getPreparedComponent($type, RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var MultiValueFieldInterface $component */
        $component = parent::getPreparedComponent($type, $renderingContext, $arguments);
        $component->setItems($arguments['items']);
        $component->setItemsProcFunc($arguments['itemsProcFunc']);
        $component->setEmptyOption($arguments['emptyOption']);
        $component->setTranslateCsvItems((boolean) $arguments['translateCsvItems']);
        $component->setMinItems($arguments['minItems']);
        $component->setMaxItems($arguments['maxItems']);
        $component->setSize($arguments['size']);
        $component->setMultiple($arguments['multiple']);
        $component->setItemListStyle($arguments['itemListStyle']);
        $component->setSelectedListStyle($arguments['selectedListStyle']);
        return $component;
    }
}
