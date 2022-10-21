<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeValidator;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentTypeValidatorNode extends AbstractNode implements NodeInterface
{
    /**
     * @var array
     */
    private $parameters = [];

    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->parameters = $data;
    }

    /**
     * @return array
     */
    public function render()
    {
        $return = $this->initializeResultArray();
        /** @var ContentTypeValidator $validator */
        $validator = GeneralUtility::makeInstance(ContentTypeValidator::class);
        $return['html'] = $validator->validateContentTypeRecord(
            $this->parameters['parameterArray'] + ['row' => $this->parameters['databaseRow']]
        );
        return $return;
    }
}
