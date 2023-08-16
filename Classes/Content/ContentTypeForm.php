<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * Content Type Form
 *
 * The Flux form used when site administrators edit record-based
 * Flux content types. Is returned from the ContentTypeProvider.
 *
 * The Grid "editor" is presented as an individual field separate
 * from the content type form.
 *
 * Grid and Form structures are managed as two completely separate
 * data structures in DB.
 */
class ContentTypeForm extends Form
{
    public function __construct()
    {
        parent::__construct();

        /** @var Form\Container\Sheet $sheet */
        $sheet = $this->createContainer(Form\Container\Sheet::class, 'sheets', 'Define sheets');
        $sheet->setDescription(
            'Define the sheets that contain form fields when editors edit your content type. Save the content type '
            . 'record to refresh changes.'
        );
        $section = $sheet->createContainer(Form\Container\Section::class, 'sheets');
        $sheetObject = $section->createContainer(Form\Container\SectionObject::class, 'sheet');
        $sheetObject->createField(
            Form\Field\Input::class,
            'name',
            'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheetName'
        );
        $sheetObject->createField(
            Form\Field\Input::class,
            'label',
            'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheetLabel'
        );
    }

    public function createSheet(string $name, string $label): void
    {
        /** @var Form\Container\Sheet $sheet */
        $sheet = $this->createContainer(Form\Container\Sheet::class, $name, $label);
        $sheet->setDescription('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheetDescription');
        $section = $sheet->createContainer(Form\Container\Section::class, 'fields', 'Fields');

        $this->createInputFieldObject($section);
        $this->createTextFieldObject($section);
        $this->createCheckboxFieldObject($section);
        $this->createRadioFieldObject($section);
        $this->createDateTimeFieldObject($section);
        $this->createSelectFieldObject($section);
        $this->createRelationFieldObject($section);
        $this->createMultiRelationFieldObject($section);
        $this->createInlineFieldObject($section);
        $this->createFileFieldObject($section);
        $this->createUserFieldObject($section);
    }

    protected function createInputFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'input',
            $this->createLabelReference('input')
        );
        $object->createField(
            Form\Field\Input::class,
            'default',
            $this->createLabelReference('universal.default.value')
        );
        $this->createUniversalFieldsInFieldObject($object);
        return $object;
    }

    protected function createTextFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'text',
            $this->createLabelReference('text')
        );
        $this->createUniversalFieldsInFieldObject($object);
        $object->createField(Form\Field\Text::class, 'default', $this->createLabelReference('universal.default.value'));
        return $object;
    }

    protected function createCheckboxFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'checkbox',
            $this->createLabelReference('checkbox')
        );
        $this->createUniversalFieldsInFieldObject($object);
        $object->createField(
            Form\Field\Checkbox::class,
            'default',
            $this->createLabelReference('universal.default.state')
        );
        return $object;
    }

    protected function createRadioFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'radio',
            $this->createLabelReference('radio')
        );
        $this->createUniversalFieldsInFieldObject($object);
        $object->createField(
            Form\Field\Input::class,
            'default',
            $this->createLabelReference('universal.default.value')
        );
        return $object;
    }

    protected function createDateTimeFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'dateTime',
            $this->createLabelReference('dateTime')
        );
        $this->createUniversalFieldsInFieldObject($object);
        return $object;
    }

    protected function createSelectFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'select',
            $this->createLabelReference('select')
        );
        $this->createUniversalFieldsInFieldObject($object);
        $object->createField(Form\Field\Input::class, 'items', $this->createLabelReference('select.items'));
        $this->createSelectAndRelationFieldsInFieldObject($object);
        return $object;
    }

    protected function createRelationFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'relation',
            $this->createLabelReference('relation')
        );
        $this->createUniversalFieldsInFieldObject($object);
        $this->createSelectAndRelationFieldsInFieldObject($object);
        return $object;
    }

    protected function createMultiRelationFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'multiRelation',
            $this->createLabelReference('multiRelation')
        );
        $this->createUniversalFieldsInFieldObject($object);
        $this->createSelectAndRelationFieldsInFieldObject($object);
        $object->createField(
            Form\Field\Input::class,
            'foreignTableField',
            $this->createLabelReference('relation.foreignTableField')
        );
        return $object;
    }

    protected function createInlineFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'inline',
            $this->createLabelReference('inline')
        );
        $this->createUniversalFieldsInFieldObject($object);
        $this->createSelectAndRelationFieldsInFieldObject($object);
        $object->createField(
            Form\Field\Input::class,
            'foreignTableField',
            $this->createLabelReference('relation.foreignTableField')
        )
        ;
        return $object;
    }

    protected function createFileFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'file',
            $this->createLabelReference('file')
        );
        $this->createUniversalFieldsInFieldObject($object);
        return $object;
    }

    protected function createUserFieldObject(Form\Container\Section $section): Form\Container\SectionObject
    {
        $object = $section->createContainer(
            Form\Container\SectionObject::class,
            'user',
            $this->createLabelReference('function')
        );
        $object->createField(
            Form\Field\Input::class,
            'userFunc',
            $this->createLabelReference('function.userFunc')
        )->setRequired(true);
        $this->createUniversalFieldsInFieldObject($object);
        return $object;
    }

    protected function createUniversalFieldsInFieldObject(Form\Container\SectionObject $object): void
    {
        $componentType = $object->getName();
        $object->createField(
            Form\Field\Input::class,
            'name',
            $this->createLabelReference('universal.name')
        )->setDefault('settings.' . $componentType)->setRequired(true);
        $object->createField(
            Form\Field\Input::class,
            'label',
            $this->createLabelReference('universal.label')
        )->setDefault('My ' . $componentType . ' field')->setRequired(true);
        $object->createField(
            Form\Field\Input::class,
            'transform',
            $this->createLabelReference('universal.transform')
        );
        $object->createField(
            Form\Field\Checkbox::class,
            'clearable',
            $this->createLabelReference('universal.clearable')
        );
    }

    protected function createSelectAndRelationFieldsInFieldObject(Form\Container\SectionObject $object): void
    {
        /** @var Form\Field\Input $input */
        $input = $object->createField(
            Form\Field\Input::class,
            'size',
            $this->createLabelReference('relation.size')
        );
        $input->setValidate('trim,int');
        $input->setSize(3);

        /** @var Form\Field\Input $input */
        $input = $object->createField(
            Form\Field\Input::class,
            'minItems',
            $this->createLabelReference('relation.minItems')
        );
        $input->setValidate('trim,int');
        $input->setSize(3);

        /** @var Form\Field\Input $input */
        $input = $object->createField(
            Form\Field\Input::class,
            'maxItems',
            $this->createLabelReference('relation.maxItems')
        );
        $input->setValidate('trim,int');
        $input->setSize(3);

        $object->createField(Form\Field\Checkbox::class, 'multiple', $this->createLabelReference('relation.multiple'));
        $object->createField(
            Form\Field\Checkbox::class,
            'emptyOption',
            $this->createLabelReference('relation.emptyOption')
        );
        $object->createField(Form\Field\Input::class, 'default', 'Default value');
    }

    protected function createLabelReference(string $label): string
    {
        return 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.fields.' . $label;
    }
}
