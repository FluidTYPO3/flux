<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeFluxTemplateDumper;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TemplateSourceDumperNode extends AbstractNode implements NodeInterface
{
    private array $parameters = [];

    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->parameters = $data;
    }

    public function render(): array
    {
        $return = $this->initializeResultArray();
        /** @var ContentTypeFluxTemplateDumper $dumper */
        $dumper = GeneralUtility::makeInstance(ContentTypeFluxTemplateDumper::class);
        $return['html'] = $dumper->dumpFluxTemplate(
            $this->parameters['parameterArray'] + ['row' => $this->parameters['databaseRow']]
        );
        return $return;
    }
}
