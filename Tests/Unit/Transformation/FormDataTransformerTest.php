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
		$instance = $this->getMock('FluidTYPO3\\Flux\\Transformation\\FormDataTransformer', ['loadObjectsFromRepository']);
		$instance->expects($this->any())->method('loadObjectsFromRepository')->willReturn([]);
		$instance->injectObjectManager($this->objectManager);
		$form = Form::create();
		$form->createField('Input', 'field')->setTransform($transformation);
		$transformed = $instance->transformAccordingToConfiguration(['field' => $value], $form);
		$this->assertTrue($transformed !== $expected, 'Transformation type ' . $transformation . ' failed; values are still identical');
	}

	/**
	 * @return array
	 */
	public function getValuesAndTransformations() {
		return [
			[['1', '2', '3'], 'integer', [1, 2, 3]],
			['0', 'integer', 0],
			['0.12', 'float', 0.12],
			['1,2,3', 'array', [1, 2, 3]],
			['123,321', 'InvalidClass', '123'],
			[date('Ymd'), 'DateTime', new \DateTime(date('Ymd'))],
			['1', 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FrontendUser', NULL],
			['1,2', 'TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage<TYPO3\\CMS\\Extbase\\Domain\\Model\\FrontendUser>', NULL],
			['1,2', 'TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage<\\Invalid>', NULL],
		];
	}

	/**
	 * @test
	 */
	public function supportsFindByIdentifiers() {
		$instance = new FormDataTransformer();
		$identifiers = ['foobar', 'foobar2'];
		$repository = $this->getMock('TYPO3\\CMS\\Extbase\\Domain\\Repository\\FrontendUserGroupRepository', ['findByUid'],
			[], '', FALSE);
		$repository->expects($this->exactly(2))->method('findByUid')->will($this->returnArgument(0));
		$result = $this->callInaccessibleMethod($instance, 'loadObjectsFromRepository', $repository, $identifiers);
		$this->assertEquals($result, ['foobar', 'foobar2']);
	}

}
