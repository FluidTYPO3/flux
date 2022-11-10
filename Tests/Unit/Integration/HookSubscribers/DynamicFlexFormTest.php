<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\HookSubscribers\DynamicFlexForm;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * DynamicFlexFormTest
 */
class DynamicFlexFormTest extends AbstractTestCase
{
    protected ?FluxService $fluxService = null;
    protected ?WorkspacesAwareRecordService $recordService = null;

    protected function setUp(): void
    {
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->setMethods(['getFromCaches', 'setInCaches', 'resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testCreatesInstancesInConstructor(): void
    {
        $subject = new DynamicFlexForm();
        self::assertInstanceOf(
            ObjectManagerInterface::class,
            $this->getInaccessiblePropertyValue($subject, 'objectManager')
        );
        self::assertInstanceOf(
            FluxService::class,
            $this->getInaccessiblePropertyValue($subject, 'configurationService')
        );
        self::assertInstanceOf(
            RecordService::class,
            $this->getInaccessiblePropertyValue($subject, 'recordService')
        );
    }

    /**
     * @return void
     */
    public function testReturnsEmptyDataStructureIdentifierForNonMatchingTableAndField()
    {
        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $subject = new DynamicFlexForm();

        $result = $subject->getDataStructureIdentifierPreProcess(
            ['foo' => 'bar'],
            'sometable',
            'somefield',
            ['uid' => 123]
        );
        $this->assertSame([], $result);
    }

    /**
     * @param array $identifier
     * @dataProvider getEmptyDataStructureIdentifierTestValues
     */
    public function testReturnsEmptyDataStructureForIdentifier(array $identifier)
    {
        $subject = $this->getMockBuilder(DynamicFlexForm::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $result = $subject->parseDataStructureByIdentifierPreProcess($identifier);
        $this->assertSame([], $result);
    }

    public function testDataStructureForIdentifierFromCache()
    {
        $structure = ['foo' => 'bar'];
        $subject = new DynamicFlexForm();
        $this->fluxService->method('getFromCaches')->willReturn($structure);
        $result = $subject->parseDataStructureByIdentifierPreProcess(['type' => 'flux', 'record' => ['uid' => 123]]);
        $this->assertSame($structure, $result);
    }

    public function testParseDataStructureForIdentifierThrowsExceptionIfUnableToLoadRecord()
    {
        $subject = $this->getMockBuilder(DynamicFlexForm::class)
            ->setMethods(['loadRecordWithoutRestriction'])
            ->getMock();
        $subject->method('loadRecordWithoutRestriction')->willReturn(null);

        self::expectExceptionCode(1668011937);
        $subject->parseDataStructureByIdentifierPreProcess(
            ['type' => 'flux', 'tableName' => 'table', 'record' => ['uid' => 123]]
        );
    }

    public function testReturnsEmptyDataStructureForIdentifierReturnsEmptyArrayWithoutProvider()
    {
        $subject = $this->getMockBuilder(DynamicFlexForm::class)
            ->setMethods(['resolvePrimaryConfigurationProvider'])
            ->getMock();
        $subject->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $result = $subject->parseDataStructureByIdentifierPreProcess(
            [
                'type' => 'flux',
                'tableName' => 'table',
                'fieldName' => 'field',
                'record' => ['uid' => 123, 'foo' => 'bar']
            ]
        );
        self::assertSame([], $result);
    }

    public function testParseDataStructureForIdentifierCachesStaticDataSourceFromProvider()
    {
        $form = Form::create();
        $form->setOption(Form::OPTION_STATIC, true);

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $subject = new DynamicFlexForm();

        $this->fluxService->expects(self::once())->method('setInCaches');
        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $result = $subject->parseDataStructureByIdentifierPreProcess(
            [
                'type' => 'flux',
                'tableName' => 'table',
                'fieldName' => 'field',
                'record' => ['uid' => 123, 'foo' => 'bar']
            ]
        );
        self::assertSame([], $result);
    }

    public function testParseDataStructureForIdentifierReturnsDataSourceFromProvider()
    {
        $form = Form::create();

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $subject = new DynamicFlexForm();

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $result = $subject->parseDataStructureByIdentifierPreProcess(
            [
                'type' => 'flux',
                'tableName' => 'table',
                'fieldName' => 'field',
                'record' => ['uid' => 123, 'foo' => 'bar']
            ]
        );
        self::assertSame(['ROOT' => ['el' => []]], $result);
    }

    /**
     * @return array
     */
    public function getEmptyDataStructureIdentifierTestValues()
    {
        return [
            [
                ['type' => 'unsupported']
            ],
            [
                ['type' => 'flux', 'record' => null]
            ],
        ];
    }

    public function testGetDataStructureIdentifierPreProcess(): void
    {
        $GLOBALS['TCA']['tt_content'] = [
            'ctrl' => [
                'type' => 'typefield',
                'useColumnsForDefaultValues' => 'pi_flexform',
                'typefield' => [
                    'subtype_value_field' => 'subtypefield',
                ],
            ],
            'columns' => [
                'pi_flexform' => [
                    'config' => [],
                ],
            ],
        ];

        $record = [
            'uid' => 0,
            'pi_flexform' => '',
        ];

        $expected = [
            'type' => 'flux',
            'tableName' => 'tt_content',
            'fieldName' => 'pi_flexform',
            'record' => [
                'pi_flexform' => '',
            ],
            'originalIdentifier' => 'ds-identifier',
        ];

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $subject = $this->getMockBuilder(DynamicFlexForm::class)
            ->setMethods(['getDataStructureIdentifier'])
            ->getMock();
        $subject->method('getDataStructureIdentifier')->willReturn('ds-identifier');

        $result = $subject->getDataStructureIdentifierPreProcess([], 'tt_content', 'pi_flexform', $record);

        unset($GLOBALS['TCA']['tt_content']['ctrl']);

        self::assertSame($expected, $result);
    }

    protected function createObjectManagerInstance(): ObjectManagerInterface
    {
        $instance = parent::createObjectManagerInstance();
        $instance->method('get')->willReturnMap(
            [
                [FluxService::class, $this->fluxService],
                [WorkspacesAwareRecordService::class, $this->recordService]
            ]
        );
        return $instance;
    }
}
