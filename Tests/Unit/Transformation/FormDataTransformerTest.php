<?php
namespace FluidTYPO3\Flux\Tests\Unit\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Transformation\FormDataTransformer;

/**
 * FormDataTransformerTest
 */
class FormDataTransformerTest extends AbstractTestCase
{

    /**
     * @test
     * @dataProvider getValuesAndTransformations
     * @param mixed $value
     * @param string $transformation
     * @param mixed $expected
     */
    public function testTransformation($value, $transformation, $expected)
    {
        $instance = $this->getMockBuilder('FluidTYPO3\\Flux\\Transformation\\FormDataTransformer')->setMethods(array('loadObjectsFromRepository'))->getMock();
        $instance->expects($this->any())->method('loadObjectsFromRepository')->willReturn(array());
        $instance->injectObjectManager($this->objectManager);
        $form = Form::create();
        $form->createField('Input', 'field')->setTransform($transformation);
        $transformed = $instance->transformAccordingToConfiguration(array('field' => $value), $form);
        $this->assertTrue($transformed !== $expected, 'Transformation type ' . $transformation . ' failed; values are still identical');
    }

    /**
     * @return array
     */
    public function getValuesAndTransformations()
    {
        return array(
            array(array('1', '2', '3'), 'integer', array(1, 2, 3)),
            array('0', 'integer', 0),
            array('0.12', 'float', 0.12),
            array('1,2,3', 'array', array(1, 2, 3)),
            array('123,321', 'InvalidClass', '123'),
            array(date('Ymd'), 'DateTime', new \DateTime(date('Ymd'))),
            array('1', 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FrontendUser', null),
            array('1,2', 'TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage<TYPO3\\CMS\\Extbase\\Domain\\Model\\FrontendUser>', null),
            array('1,2', 'TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage<\\Invalid>', null),
        );
    }

    /**
     * @test
     */
    public function supportsFindByIdentifiers()
    {
        $instance = new FormDataTransformer();
        $identifiers = array('foobar', 'foobar2');
        $repository = $this->getMockBuilder(
            'TYPO3\\CMS\\Extbase\\Domain\\Repository\\FrontendUserGroupRepository'
        )->setMethods(
            array('findByUid')
        )->disableOriginalConstructor()->getMock();
        $repository->expects($this->exactly(2))->method('findByUid')->will($this->returnArgument(0));
        $result = $this->callInaccessibleMethod($instance, 'loadObjectsFromRepository', $repository, $identifiers);
        $this->assertEquals($result, array('foobar', 'foobar2'));
    }
}
