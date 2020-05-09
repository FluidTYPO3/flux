<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeFluxTemplateDumper;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TemplateSourceDumperNode extends AbstractNode implements NodeInterface
{
    private $parameters = [];

    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->parameters = $data;
    }

    public function render()
    {
        $return = $this->initializeResultArray();
        $return['html'] = GeneralUtility::makeInstance(ContentTypeFluxTemplateDumper::class)->dumpFluxTemplate(
            $this->parameters['parameterArray'] + ['row' => $this->parameters['databaseRow']]
        );
        return $return;
    }
}
