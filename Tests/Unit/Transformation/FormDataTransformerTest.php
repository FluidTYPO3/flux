<?php
namespace FluidTYPO3\Flux\Transformation;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * Transforms data according to settings defined in the Form instance.
 *
 * @package FluidTYPO3\Flux
 */
class FormDataTransformerTest extends AbstractTestCase {

	/**
	 * @test
	 * @dataProvider getValuesAndTransformations
	 * @param mixed $value
	 * @param string $transformation
	 * @param mixed $expected
	 */
	public function testTransformation($value, $transformation, $expected) {
		$instance = $this->createInstance();
		$form = Form::create();
		$form->createField('Input', 'field')->setTransform($transformation);
		$transformed = $instance->transformAccordingToConfiguration(array('field' => $value), $form);
		$this->assertTrue($transformed !== $expected, 'Transformation type ' . $transformation . ' failed; values are still identical');
	}

	/**
	 * @return array
	 */
	public function getValuesAndTransformations() {
		return array(
			array(array('1', '2', '3'), 'integer', array(1, 2, 3)),
			array('0', 'integer', 0),
			array('0.12', 'float', 0.12),
			array('1,2,3', 'array', array(1, 2, 3)),
			array(date('Ymd'), 'DateTime', new \DateTime(date('Ymd'))),
			array('99999', 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FrontendUser', NULL),
			array('99998,99999', 'TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage<\\TYPO3\\CMS\\Extbase\\Domain\\Model\\FrontendUser>', NULL),
			array('99998,99999', 'TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage<\\Invalid>', NULL),
			array('123,321', 'InvalidClass', '123'),
		);
	}

	/**
	 * @test
	 */
	public function supportsFindByIdentifiers() {
		$instance = new FormDataTransformer();
		$identifiers = array(123);
		$repository = $this->getMock('TYPO3\\CMS\\Extbase\\Domain\\Repository\\FrontendUserGroupRepository', array('findByIdentifiers'), array(), '', FALSE);
		$repository->expects($this->once())->method('findByIdentifiers')->will($this->returnValue('foobar'));
		$result = $this->callInaccessibleMethod($instance, 'loadObjectsFromRepository', $repository, $identifiers);
		$this->assertEquals($result, 'foobar');
	}

}
