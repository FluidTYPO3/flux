<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\DynamicFlexForm;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * DynamicFlexFormTest
 */
class DynamicFlexFormTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function canExecuteDataStructurePostProcessHook()
    {
        $this->canExecuteDataStructurePostProcessHookInternal();
    }

    /**
     * @test
     */
    public function canExecuteDataStructurePostProcessHookWithNullFieldName()
    {
        $this->canExecuteDataStructurePostProcessHookInternal(null);
    }

    /**
     * @test
     */
    public function canExecuteDataStructurePostProcessHookWithNullFieldAndBadTableName()
    {
        $this->canExecuteDataStructurePostProcessHookInternal(null, 'badtablename');
    }

    /**
     * @param string $fieldName
     * @param string $table
     * @return void
     */
    protected function canExecuteDataStructurePostProcessHookInternal($fieldName = 'pi_flexform', $table = 'tt_content')
    {
        $dataStructure = array();
        $config = array();
        $row = array($fieldName => '');
        $instance = new DynamicFlexForm();
        $provider1 = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('postProcessDataStructure', 'getForm'))->getMock();
        $provider2 = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('postProcessDataStructure', 'getForm'))->getMock();
        $provider1->expects($this->any())->method('postProcessDataStructure');
        $provider2->expects($this->any())->method('postProcessDataStructure');
        $provider1->expects($this->any())->method('getForm')->with($row)->willReturn(Form::create());
        $provider2->expects($this->any())->method('getForm')->with($row)->willReturn(Form::create());
        $providers = array($provider1, $provider2);
        $service = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('resolveConfigurationProviders'))->getMock();
        $service->expects($this->any())->method('resolveConfigurationProviders')
            ->with($table, $fieldName, $row)->willReturn($providers);
        $instance->injectConfigurationService($service);
        $instance->getFlexFormDS_postProcessDS($dataStructure, $config, $row, $table, $fieldName);
        $isArrayConstraint = new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
        $this->assertThat($dataStructure, $isArrayConstraint);
    }
}
