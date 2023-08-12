<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\Statement;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleDataViewHelper;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataViewHelperTest extends AbstractViewHelperTestCase
{
    protected ?FluxService $fluxService;
    protected ?ProviderResolver $providerResolver;
    protected ?WorkspacesAwareRecordService $recordService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->onlyMethods(['convertFlexFormContentToArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->getMock();
        AccessibleDataViewHelper::setFluxService($this->fluxService);
        AccessibleDataViewHelper::setProviderResolver($this->providerResolver);
        AccessibleDataViewHelper::setRecordService($this->recordService);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        AccessibleDataViewHelper::setFluxService(null);
        AccessibleDataViewHelper::setProviderResolver(null);
        AccessibleDataViewHelper::setRecordService(null);
    }

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'columns' => [
                    'pi_flexform' => []
                ]
            ],
            'be_users' => [
                'columns' => [
                    'username' => []
                ]
            ],
        ];
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        unset($GLOBALS['TCA']);
    }

    /**
     * @test
     */
    public function failsWithInvalidTable()
    {
        $arguments = [
            'table' => 'invalid',
            'field' => 'pi_flexform',
            'uid' => 1
        ];
        $viewHelper = $this->buildViewHelperInstance($arguments);

        $this->expectViewHelperException(
            'Invalid table:field "' . $arguments['table'] . ':' . $arguments['field'] . '" - does not exist in TYPO3 TCA.'
        );

        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function failsWithMissingArguments()
    {
        $arguments = [
            'table' => 'tt_content',
            'field' => 'pi_flexform',
        ];

        $statement = $this->getMockBuilder(Statement::class)
            ->addMethods(['fetchAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $statement->method('fetchAll')->willReturn([]);

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->onlyMethods(['select', 'from', 'where', 'execute'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('execute')->willReturn($statement);
        $connectionPool = $this->getMockBuilder(ConnectionPool::class)
            ->onlyMethods(['getQueryBuilderForTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $connectionPool->method('getQueryBuilderForTable')->with('tt_content')->willReturn($queryBuilder);

        GeneralUtility::addInstance(ConnectionPool::class, $connectionPool);

        $this->expectViewHelperException('dummy');
        $this->executeViewHelper($arguments);
    }

    /**
     * @test
     */
    public function failsWithInvalidField()
    {
        $arguments = [
            'table' => 'tt_content',
            'field' => 'invalid',
            'uid' => 1
        ];
        $this->expectViewHelperException('dummy');
        $this->executeViewHelper($arguments);
    }

    /**
     * @test
     */
    public function canExecuteViewHelper()
    {
        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([]);
        $arguments = [
            'table' => 'tt_content',
            'field' => 'pi_flexform',
            'record' => [
                'foo' => 'bar',
                'pi_flexform' => '',
            ],
        ];

        $this->assertSame(
            [],
            $this->executeViewHelper($arguments)
        );
    }

    /**
     * @test
     */
    public function canUseRecordAsArgument()
    {
        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([]);
        $record = Records::$contentRecordIsParentAndHasChildren;
        $record['pi_flexform'] = '';
        $arguments = [
            'table' => 'tt_content',
            'field' => 'pi_flexform',
            'record' => $record
        ];
        $result = $this->executeViewHelper($arguments);
        $this->assertIsArray($result);
    }

    /**
     * @test
     */
    public function canUseChildNodeAsRecord()
    {
        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([]);
        $arguments = [
            'table' => 'tt_content',
            'field' => 'pi_flexform',
            'uid' => 1
        ];
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $record['pi_flexform'] = '';
        $content = $this->createNode('Array', $record);
        $viewHelper = $this->buildViewHelperInstance($arguments, [], $content);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertIsArray($output);
    }

    /**
     * @test
     */
    public function supportsAsArgument()
    {
        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([]);
        $this->fluxService->method('convertFlexFormContentToArray')->willReturn([]);
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $row['pi_flexform'] = $row['test'];
        $arguments = [
            'record' => $row,
            'table' => 'tt_content',
            'field' => 'pi_flexform',
            'as' => 'test'
        ];
        $output = $this->executeViewHelperUsingTagContent('Some text', $arguments);
        $this->assertEquals($output, 'Some text');
    }

    /**
     * @test
     */
    public function supportsAsArgumentAndBacksUpExistingVariable()
    {
        $this->templateVariableContainer->add('test', 'test');
        $this->supportsAsArgument();
        self::assertSame('test', $this->templateVariableContainer->get('test'));
    }

    /**
     * @test
     */
    public function readDataArrayFromProvidersOrUsingDefaultMethodCallsConfigurationServiceConvertOnEmptyProviderArray()
    {
        $this->providerResolver->method('resolveConfigurationProviders')->willReturn([]);
        $this->fluxService->method('convertFlexFormContentToArray')->willReturn([]);
        $mock = $this->createInstance();
        $providers = [];
        $record = ['test' => ''];
        $field = 'test';
        $result = $this->callInaccessibleMethod(
            $mock,
            'readDataArrayFromProvidersOrUsingDefaultMethod',
            $providers,
            $record,
            $field
        );
        $this->assertSame([], $result);
    }

    /**
     * @test
     */
    public function readDataArrayFromProvidersOrUsingDefaultMethodUsesProvidersToReadData()
    {
        $mock = $this->createInstance();
        $provider1 = $this->getMockBuilder(Provider::class)
            ->onlyMethods(['getFlexFormValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider1->method('getFlexFormValues')->willReturn(['foo' => ['bar' => 'test']]);
        $provider2 = $this->getMockBuilder(Provider::class)
            ->onlyMethods(['getFlexFormValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider2->method('getFlexFormValues')->willReturn(
            ['foo' => ['bar' => 'test2', 'baz' => 'test'], 'bar' => 'test']
        );
        $providers = [$provider1, $provider2];
        $record = Records::$contentRecordIsParentAndHasChildren;
        $field = 'pi_flexform';
        $result = $this->callInaccessibleMethod(
            $mock,
            'readDataArrayFromProvidersOrUsingDefaultMethod',
            $providers,
            $record,
            $field
        );
        $this->assertEquals(['foo' => ['bar' => 'test2', 'baz' => 'test'], 'bar' => 'test'], $result);
    }
}
