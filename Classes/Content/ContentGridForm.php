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
 * Content Grid Form
 *
 * Provides a Form that allows site administrators to set up a
 * grid structure that is saved along with a content type.
 *
 * The Grid "editor" is presented as an individual field separate
 * from the ContentTypeForm.
 */
class ContentGridForm extends Form
{
    public function __construct()
    {
        parent::__construct();

        $this->createContainer(Form\Container\Sheet::class, 'grid');
        $gridMode = $this->createField(Form\Field\Select::class, 'gridMode', 'Grid mode');
        $gridModeOptions = [Form\Container\Section::GRID_MODE_ROWS, Form\Container\Section::GRID_MODE_COLUMNS];
        $gridMode->setDefault(Form\Container\Section::GRID_MODE_ROWS);
        $gridMode->setItems(array_combine($gridModeOptions, $gridModeOptions));
        /** @var Form\Field\Input $autoColumns */
        $autoColumns = $this->createField(Form\Field\Input::class, 'autoColumns', 'Automatic content columns (adds automatic columns AFTER those defined below, until this number of total columns is reached)');
        $autoColumns->setValidate('trim,int');
        $autoColumns->setSize(3);
        $section = $this->createContainer(Form\Container\Section::class, 'columns', 'Manual content columns');
        $columnField = $section->createContainer(Form\Container\SectionObject::class, 'column');
        $columnField->createField(Form\Field\ColumnPosition::class, 'colPos', 'Column position value');
    }

}
