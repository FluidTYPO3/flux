<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Attribute\DataTransformer;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\Transformer\ObjectTransformer;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ObjectTransformerTest extends AbstractTestCase
{
    private ?FrontendUserRepository $frontendUserRepository = null;
    private ?FrontendUser $frontendUser = null;
    private ObjectTransformer $subject;

    protected function setUp(): void
    {
        $this->singletonInstances[Repository::class] = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->getMockBuilder(ObjectTransformer::class)
            ->onlyMethods(['loadObjectsFromRepository'])
            ->getMock();

        parent::setUp();
    }

    private function initializeFrontendUserFixtures(): void
    {
        $this->frontendUser = $this->getMockBuilder(FrontendUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->frontendUserRepository = $this->getMockBuilder(FrontendUserRepository::class)
            ->setMethods(['findByUid'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontendUserRepository->method('findByUid')->willReturn($this->frontendUser);

        GeneralUtility::setSingletonInstance(FrontendUserRepository::class, $this->frontendUserRepository);
    }

    public function testGetPriority(): void
    {
        self::assertSame(0, $this->subject->getPriority());
    }

    /**
     * @dataProvider getCanTransformToTypeTestValues
     */
    public function testCanTransformToType(bool $expected, string $type): void
    {
        self::assertSame($expected, $this->subject->canTransformToType($type));
    }

    public function getCanTransformToTypeTestValues(): array
    {
        $domainObjectClass = get_class($this->getMockBuilder(DomainObjectInterface::class)->getMockForAbstractClass());
        return [
            'supports standard object' => [true, DataTransformer::class],
            'supports single domain object' => [true, $domainObjectClass],
            'supports compound with domain object' => [true, ObjectStorage::class . '<' . $domainObjectClass . '>'],
            'supports compound with other object' => [true, ObjectStorage::class . '<' . DataTransformer::class . '>'],
            'does not support float' => [false, 'float'],
            'does not support string' => [false, 'string'],
        ];
    }

    public function testConvertsToSimpleObjectsThroughConstructor(): void
    {
        $input = Form::create()->createField(Form\Field\Input::class, 'test');
        $output = $this->subject->transform($input, DataTransformer::class, 'test-id');
        self::assertInstanceOf(DataTransformer::class, $output);
        self::assertSame('test-id', $output->identifier);
    }

    /**
     * @dataProvider getValuesAndTransformationsForDomainObjects
     * @param mixed $return
     * @param mixed $value
     * @param mixed $expected
     */
    public function testTransformationOfDomainObjects($value, string $transformation, $return, $expected): void
    {
        if (!class_exists(FrontendUser::class)) {
            $this->markTestSkipped('Skipping test with FrontendUser dependency');
        }

        $this->initializeFrontendUserFixtures();
        $this->subject->method('loadObjectsFromRepository')->willReturn($return);
        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        $component = $form->createField(Form\Field\Input::class, 'field')->setTransform($transformation);
        $transformed = $this->subject->transform($component, $transformation, $value);
        $this->assertSame($expected, $transformed);
    }

    public function getValuesAndTransformationsForDomainObjects(): array
    {
        return [
            ['1,2', ObjectStorage::class . '<' . FrontendUser::class . '>', [], []],
            ['1', FrontendUser::class, [$this->frontendUser], $this->frontendUser],
        ];
    }

    public function testSupportsFindByIdentifiers(): void
    {
        if (!class_exists(FrontendUser::class)) {
            $this->markTestSkipped('Skipping test with FrontendUser dependency');
        }
        $repository = $this->getMockBuilder(FrontendUserGroupRepository::class)
            ->onlyMethods(['findByUid'])
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(2))->method('findByUid')->willReturnArgument(0);

        $identifiers = ['foobar', 'foobar2'];

        $result = $this->callInaccessibleMethod(
            new ObjectTransformer(),
            'loadObjectsFromRepository',
            $repository,
            $identifiers
        );
        $this->assertEquals($identifiers, $result);
    }
}
