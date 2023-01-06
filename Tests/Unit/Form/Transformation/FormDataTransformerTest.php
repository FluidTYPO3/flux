<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * FormDataTransformerTest
 */
class FormDataTransformerTest extends AbstractTestCase
{
    private ?FrontendUserRepository $frontendUserRepository = null;
    private ?FrontendUser $frontendUser = null;

    protected function setUp(): void
    {
        if (!class_exists(FrontendUser::class)) {
            $this->markTestSkipped('Skipping test with FrontendUser dependency');
        }

        parent::setUp();

        $this->frontendUser = $this->getMockBuilder(FrontendUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->frontendUserRepository = $this->getMockBuilder(FrontendUserRepository::class)
            ->setMethods(['findByUid'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontendUserRepository->method('findByUid')->willReturn($this->frontendUser);

        $this->singletonInstances[FrontendUserRepository::class] = $this->frontendUserRepository;
        $this->singletonInstances[Repository::class] = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function fixtureTransformToFooString(): string
    {
        return 'foo';
    }

    /**
     * @dataProvider getValuesAndTransformations
     * @param mixed $value
     * @param mixed $expected
     */
    public function testTransformation($value, string $transformation, $expected)
    {
        $instance = $this->getMockBuilder(FormDataTransformer::class)
            ->setMethods(['loadObjectsFromRepository'])
            ->getMock();
        $instance->method('loadObjectsFromRepository')->willReturn([]);
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField(Form\Field\Input::class, 'field')->setTransform($transformation);
        $transformed = $instance->transformAccordingToConfiguration(['field' => $value], $form);
        $this->assertNotSame(
            $expected,
            $transformed,
            'Transformation type ' . $transformation . ' failed; values are still identical'
        );
    }

    public function getValuesAndTransformations(): array
    {
        return [
            array(array('1', '2', '3'), 'integer', array(1, 2, 3)),
            array('0', 'integer', 0),
            array('0.12', 'float', 0.12),
            array('1,2,3', 'array', array(1, 2, 3)),
            array('123,321', 'InvalidClass', '123'),
            array(date('Ymd'), 'DateTime', new \DateTime(date('Ymd'))),
            array('1', 'boolean', true),
            array('1,2', ObjectStorage::class . '<' . FrontendUser::class . '>', null),
            array('1,2', ObjectStorage::class . '<\\Invalid>', null),
            array('bar', self::class . '->fixtureTransformToFooString', 'foo'),
            array('1', FrontendUser::class, $this->frontendUser),
        ];
    }

    public function testSupportsFindByIdentifiers(): void
    {
        $repository = $this->getMockBuilder(FrontendUserGroupRepository::class)
            ->setMethods(array('findByUid'))
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(2))->method('findByUid')->willReturnArgument(0);

        $instance = new FormDataTransformer();
        $identifiers = array('foobar', 'foobar2');

        $result = $this->callInaccessibleMethod(
            $instance,
            'loadObjectsFromRepository',
            $repository,
            $identifiers
        );
        $this->assertEquals($result, array('foobar', 'foobar2'));
    }
}
