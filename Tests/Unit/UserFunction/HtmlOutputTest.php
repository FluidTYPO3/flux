<?php
namespace FluidTYPO3\Flux\Tests\Unit\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\FormEngine;

/**
 * HtmlOutputTest
 */
class HtmlOutputTest extends AbstractUserFunctionTest
{

    /**
     * @var array
     */
    protected $parameters = array(
        'parameters' => array()
    );

    /**
     * @return array
     */
    protected function getParameters()
    {
        $self = $this;
        $parameters = $this->parameters;
        $parameters['parameters']['closure'] = function ($params) use ($self) {
            return 'I am a closure: ' . var_export($params, true);
        };
        return $parameters;
    }
}
