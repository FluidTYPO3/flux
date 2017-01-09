<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * LinkTest
 */
class LinkTest extends AbstractWizardTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'test',
        'label' => 'Test field',
        'hideParent' => false,
        'blindLinkOptions' => array('new', 'info'),
        'blindLinkFields' => array('title', 'uid'),
        'allowedExtensions' => array('pdf', 'txt'),
        'height' => 400,
        'width' => 300
    );

    /**
     * @test
     */
    public function canUseStringAsBlindLinkOptionsList()
    {
        $extensions = implode(',', $this->chainProperties['blindLinkOptions']);
        $instance = $this->createInstance();
        $fetched = $instance->setBlindLinkOptions($extensions)->getBlindLinkOptions();
        $this->assertSame($this->chainProperties['blindLinkOptions'], $fetched);
    }

    /**
     * @test
     */
    public function canUseStringAsBlindLinkFieldsList()
    {
        $extensions = implode(',', $this->chainProperties['blindLinkFields']);
        $instance = $this->createInstance();
        $fetched = $instance->setBlindLinkFields($extensions)->getBlindLinkFields();
        $this->assertSame($this->chainProperties['blindLinkFields'], $fetched);
    }

    /**
     * @test
     */
    public function canUseStringAsAllowedExtensionList()
    {
        $extensions = implode(',', $this->chainProperties['allowedExtensions']);
        $instance = $this->createInstance();
        $fetched = $instance->setAllowedExtensions($extensions)->getAllowedExtensions();
        $this->assertSame($this->chainProperties['allowedExtensions'], $fetched);
    }
}
