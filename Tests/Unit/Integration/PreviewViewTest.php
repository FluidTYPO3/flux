<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * PreviewViewTest
 */
class PreviewViewTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testGetOptionModeReturnsDefaultIfNoValidOptionsFound()
    {
        $instance = $this->createInstance();
        $options = array(PreviewView::OPTION_MODE => 'someinvalidvalue');
        $result = $this->callInaccessibleMethod($instance, 'getOptionMode', $options);
        $this->assertEquals(PreviewView::MODE_APPEND, $result);
    }

    /**
     * @test
     */
    public function returnsDefaultsWithoutForm()
    {
        $instance = $this->createInstance();
        $result = $this->callInaccessibleMethod($instance, 'getPreviewOptions');
        $this->assertEquals(array(
            PreviewView::OPTION_MODE => PreviewView::MODE_APPEND,
            PreviewView::OPTION_TOGGLE => true,
        ), $result);
    }

    /**
     * @test
     */
    public function avoidsRenderPreviewSectionIfTemplateFileDoesNotExist()
    {
        $provider = $this->getMockBuilder(Provider::class)->setMethods(array('getTemplatePathAndFilename'))->getMock();
        $provider->expects($this->atLeastOnce())->method('getTemplatePathAndFilename')->willReturn(null);
        $previewView = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('dummy'))->getMock();
        $this->callInaccessibleMethod($previewView, 'renderPreviewSection', $provider, array());
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $instance = $this->getMockBuilder(PreviewView::class)->setMethods(['configurePageLayoutViewForLanguageMode'])->getMock();
        $instance->expects($this->any())->method('configurePageLayoutViewForLanguageMode')->willReturnArgument(0);
        return $instance;
    }
}
