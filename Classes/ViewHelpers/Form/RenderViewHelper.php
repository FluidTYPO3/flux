<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Fluidbackend project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ## Main form rendering ViewHelper
 *
 * Use to render a Flux form as HTML.
 */
class RenderViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var \FluidTYPO3\Flux\Service\FluxService
     * @inject
     */
    protected $configurationService;

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @param Form $form
     * @return string
     */
    public function render(Form $form)
    {
        $record = $form->getOption(Form::OPTION_RECORD);
        $table = $form->getOption(Form::OPTION_RECORD_TABLE);
        $field = $form->getOption(Form::OPTION_RECORD_FIELD);
        $node = $this->getNodeFactory()->create([
            'type' => 'flex',
            'renderType' => 'flex',
            'flexFormDataStructureArray' => $form->build(),
            'tableName' => $table,
            'fieldName' => $field,
            'databaseRow' => $record,
            'inlineStructure' => [],
            'parameterArray' => [
                'itemFormElName' => sprintf('data[%s][%d][%s]', $table, (integer) $record['uid'], $field),
                'itemFormElValue' => GeneralUtility::xml2array($record[$field]),
                'fieldChangeFunc' => [],
                'fieldConf' => [
                    'config' => [
                        'ds' => $form->build(),
                    ],
                ],
            ],
        ]);
        $output = $node->render();
        return $output['html'];
    }

    /**
     * @return NodeFactory
     */
    protected function getNodeFactory()
    {
        return new NodeFactory();
    }
}
