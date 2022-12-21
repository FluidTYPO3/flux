<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Conversion;

use FluidTYPO3\Flux\Form;

interface FormConverterInterface
{
    /**
     * @return mixed
     */
    public function convertFormAndGrid(Form $form, Form\Container\Grid $grid, array $configuration);
}
