<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field\Inline;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Inline\Fal;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field\AbstractFieldViewHelperTestCase;

class FalViewHelperTest extends AbstractFieldViewHelperTestCase
{
    /**
     * @test
     */
    public function createsExpectedComponent()
    {
        $arguments = [
            'name' => 'test'
        ];
        $instance = $this->buildViewHelperInstance($arguments, []);
        $component = $instance->getComponent(
            $this->renderingContext,
            $this->buildViewHelperArguments($instance, $arguments)
        );
        $this->assertInstanceOf(Fal::class, $component);
    }

    /**
     * @test
     */
    public function supportsHeaderThumbnail()
    {
        $arguments = [
            'name' => 'test',
            'headerThumbnail' => ['test' => 'test']
        ];
        $instance = $this->buildViewHelperInstance($arguments, []);
        $component = $instance->getComponent(
            $this->renderingContext,
            $this->buildViewHelperArguments($instance, $arguments)
        );
        $this->assertEquals($arguments['headerThumbnail'], $component->getHeaderThumbnail());
    }

    /**
     * @test
     */
    public function supportsForeignMatchFields()
    {
        $arguments = [
            'name' => 'test',
            'foreignMatchFields' => ['test' => 'test']
        ];
        $instance = $this->buildViewHelperInstance($arguments, []);
        $component = $instance->getComponent(
            $this->renderingContext,
            $this->buildViewHelperArguments($instance, $arguments)
        );
        $this->assertEquals($arguments['foreignMatchFields'], $component->getForeignMatchFields());
    }

    /**
     * @test
     */
    public function supportsCropVariants()
    {
        $arguments = [
            'name' => 'test',
            'cropVariants' => ['test' => 'test']
        ];
        $instance = $this->buildViewHelperInstance($arguments, []);
        $component = $instance->getComponent(
            $this->renderingContext,
            $this->buildViewHelperArguments($instance, $arguments)
        );
        $this->assertEquals($arguments['cropVariants'], $component->getCropVariants());
    }
}
