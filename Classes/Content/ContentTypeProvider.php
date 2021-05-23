<?php
namespace FluidTYPO3\Flux\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Interfaces\ContentTypeProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\PreviewProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Flux Provider for content_types table
 *
 * Provides Flux integration for the table which carries
 * Flux-based content type definitions. Is mainly responsible
 * for providing a (dynamic) Flux form that allows site admin
 * to configure properties of a content type record.
 */
class ContentTypeProvider extends AbstractProvider implements
    RecordProviderInterface,
    PreviewProviderInterface,
    ContentTypeProviderInterface,
    DataStructureProviderInterface,
    FormProviderInterface
{
    protected $tableName = 'content_types';
    protected $fieldName = 'content_configuration';
    protected $extensionKey = 'FluidTYPO3.Builder';

    public function trigger(array $row, $table, $field, $extensionKey = null)
    {
        return $table === $this->tableName && ($field === $this->fieldName || $field === null);
    }

    public function getForm(array $row)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $contentType = $objectManager->get(RecordBasedContentTypeDefinition::class, $row);
        if (!$contentType) {
            throw new \RuntimeException(
                sprintf('Content type "%s" from record UID "%d" is not managed by Flux', $row['CType'], $row['uid'])
            );
        }
        $form = $objectManager->get(ContentTypeForm::class);
        foreach ($contentType->getSheetNamesAndLabels() as $name => $label) {
            $form->createSheet($name, $label);
        }
        return $form;
    }
}
