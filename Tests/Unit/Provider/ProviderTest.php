<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;

/**
 * ProviderTest
 */
class ProviderTest extends AbstractProviderTest
{

    /**
     * @var array
     */
    protected $definition = array(
        'name' => 'test',
        'label' => 'Test provider',
        'tableName' => 'tt_content',
        'fieldName' => 'pi_flexform',
        'form' => array(
            'sheets' => array(
                'foo' => array(
                    'fields' => array(
                        'test' => array(
                            'type' => 'Input',
                        )
                    )
                ),
                'bar' => array(
                    'fields' => array(
                        'test2' => array(
                            'type' => 'Input',
                        )
                    )
                ),
            ),
            'fields' => array(
                'test3' => array(
                    'type' => 'Input',
                )
            ),
        ),
        'grid' => array(
            'rows' => array(
                'foo' => array(
                    'columns' => array(
                        'bar' => array(
                            'areas' => array(

                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * @test
     */
    public function canGetName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $provider->loadSettings($this->definition);
        $this->assertSame($provider->getName(), $this->definition['name']);
    }

    /**
     * @test
     */
    public function canCreateInstanceWithListType()
    {
        $definition = $this->definition;
        $definition['listType'] = 'felogin_pi1';
        $provider = $this->getConfigurationProviderInstance();
        $provider->loadSettings($definition);
        $this->assertSame($provider->getName(), $definition['name']);
        $this->assertSame($provider->getListType(), $definition['listType']);
    }

    /**
     * @test
     */
    public function canReturnExtensionKey()
    {
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $service = $this->createFluxServiceInstance();
        $provider = new Provider();
        $provider->setExtensionKey('test');
        $resolver = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\ProviderResolver')->setMethods(array('resolvePrimaryConfigurationProvider'))->getMock();
        $resolver->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $service->injectProviderResolver($resolver);
        $result = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', array(), 'flux');
        $this->assertSame($provider, $result);
        $extensionKey = $result->getExtensionKey($record);
        $this->assertNotEmpty($extensionKey);
        $this->assertRegExp('/[a-z_]+/', $extensionKey);
    }

    /**
     * @test
     */
    public function canReturnPathSetByRecordWithoutParentAndWithoutChildren()
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $service = $this->createFluxServiceInstance();
        $provider = new Provider();
        $provider->setTemplatePaths(array());
        $resolver = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\ProviderResolver')->setMethods(array('resolvePrimaryConfigurationProvider'))->getMock();
        $resolver->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $service->injectProviderResolver($resolver);
        $result = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', $row);
        $this->assertSame($result, $provider);
        $paths = $result->getTemplatePaths($row);
        $this->assertIsArray($paths);
    }

    /**
     * @test
     */
    public function canCreateFormFromDefinitionWithAllSupportedNodes()
    {
        /** @var ProviderInterface $instance */
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $provider->loadSettings($this->definition);
        $form = $provider->getForm($record);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
    }

    /**
     * @test
     */
    public function canCreateGridFromDefinitionWithAllSupportedNodes()
    {
        /** @var ProviderInterface $instance */
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $provider->loadSettings($this->definition);
        $grid = $provider->getGrid($record);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $grid);
    }
}
