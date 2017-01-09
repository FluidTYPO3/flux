<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

/**
 * TextTest
 */
class TextTest extends InputTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'test',
        'label' => 'Test field',
        'enable' => true,
        'maxCharacters' => 30,
        'maximum' => 10,
        'minimum' => 0,
        'validate' => 'trim,int',
        'default' => 'test',
        'columns' => 85,
        'rows' => 8,
        'requestUpdate' => true
    );

    /**
     * @test
     */
    public function canChainSetterForEnableRichText()
    {
        /** @var Text $instance */
        $instance = $this->createInstance();
        $chained = $instance->setEnableRichText(true);
        $this->assertSame($instance, $chained);
        $this->assertTrue($instance->getEnableRichText());
    }

    /**
     * @test
     */
    public function canChainSetterForDefaultExtras()
    {
        /** @var Text $instance */
        $instance = $this->createInstance();
        $chained = $instance->setDefaultExtras('void');
        $this->assertSame($instance, $chained);
        $this->assertSame('void', $instance->getDefaultExtras());
    }

    /**
     * @test
     */
    public function canBuildConfigurationWithoutDefaultExtrasWithEnableRichText()
    {
        /** @var Text $instance */
        $instance = $this->createInstance();
        $instance->setDefaultExtras(null)->setEnableRichText(true);
        $result = $this->performTestBuild($instance);
        $this->assertArrayHasKey('defaultExtras', $result['config']);
    }

    /**
     * @test
     */
    public function canBuildConfigurationWithDefaultExtras()
    {
        /** @var Text $instance */
        $instance = $this->createInstance();
        $instance->setDefaultExtras('richtext[*]');
        $result = $this->performTestBuild($instance);
        $this->assertNotEmpty($result['defaultExtras']);
    }
}
