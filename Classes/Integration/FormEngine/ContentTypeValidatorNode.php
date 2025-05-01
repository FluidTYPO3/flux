<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeValidator;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentTypeValidatorNode extends AbstractNode implements NodeInterface
{
    public function render(): array
    {
        $return = $this->initializeResultArray();
        /** @var ContentTypeValidator $validator */
        $validator = GeneralUtility::makeInstance(ContentTypeValidator::class);
        $return['html'] = $validator->validateContentTypeRecord(
            $this->data['parameterArray'] + ['row' => $this->data['databaseRow']]
        );
        return $return;
    }
}
