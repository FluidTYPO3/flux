<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Interfaces\ContentTypeProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\PreviewProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    protected ?string $tableName = 'content_types';
    protected ?string $fieldName = 'content_configuration';
    protected string $extensionKey = 'FluidTYPO3.Flux';

    public function trigger(array $row, ?string $table, ?string $field, ?string $extensionKey = null): bool
    {
        return $table === $this->tableName && ($field === $this->fieldName || $field === null);
    }

    public function getForm(array $row, ?string $forField = null): ?Form
    {
        $contentType = $this->resolveContentTypeDefinition($row);
        /** @var ContentTypeForm $form */
        $form = GeneralUtility::makeInstance(ContentTypeForm::class);
        /** @var string[] $labels */
        $labels = $contentType->getSheetNamesAndLabels();
        foreach ($labels as $name => $label) {
            $form->createSheet($name, $label);
        }
        return $form;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function resolveContentTypeDefinition(array $row): RecordBasedContentTypeDefinition
    {
        /** @var RecordBasedContentTypeDefinition $contentType */
        $contentType = GeneralUtility::makeInstance(RecordBasedContentTypeDefinition::class, $row);
        return $contentType;
    }
}
