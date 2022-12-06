<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProviderTest extends AbstractProviderTest
{
    protected array $definition = array(
        'name' => 'test',
        'label' => 'Test provider',
        'tableName' => 'tt_content',
        'fieldName' => 'pi_flexform',
        'form' => array(
            'sheets' => array(
                'foo' => array(
                    'fields' => array(
                        'test' => array(
                            'type' => Input::class,
                        )
                    )
                ),
                'bar' => array(
                    'fields' => array(
                        'test2' => array(
                            'type' => Input::class,
                        )
                    )
                ),
            ),
            'fields' => array(
                'test3' => array(
                    'type' => Input::class,
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
        $resolver = $this->getMockBuilder(ProviderResolver::class)->setMethods(['resolvePrimaryConfigurationProvider'])->getMock();
        $resolver->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        GeneralUtility::setSingletonInstance(ProviderResolver::class, $resolver);

        $result = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', [], 'flux');
        $this->assertSame($provider, $result);
        $extensionKey = $result->getExtensionKey($record);
        $this->assertNotEmpty($extensionKey);
        $this->assertMatchesRegularExpression('/[a-z_]+/', $extensionKey);
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
        $this->assertInstanceOf(Grid::class, $grid);
    }
}
