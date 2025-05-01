<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class TypoScriptServiceTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testGetSettingsForExtensionName()
    {
        $instance = $this->getMockBuilder(TypoScriptService::class)
            ->onlyMethods(['getTypoScriptByPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance->expects($this->once())->method('getTypoScriptByPath')
            ->with('plugin.tx_underscore.settings')
            ->willReturn(['test' => 'test']);
        $result = $instance->getSettingsForExtensionName('under_score');
        $this->assertEquals(['test' => 'test'], $result);
    }

    /**
     * @test
     */
    public function testGetTypoScriptByPath()
    {
        $cacheService = $this->getMockBuilder(CacheService::class)
            ->onlyMethods(['setInCaches', 'getFromCaches', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();
        $configurationManager->method('getConfiguration')->willReturn(
            [
                'plugin' => [
                    'tx_test' => [
                        'settings' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
            ]
        );

        $service = new TypoScriptService($cacheService, $configurationManager);

        $result = $service->getTypoScriptByPath('plugin.tx_test.settings');
        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testGetTypoScriptByPathSwallowsSpecificException(): void
    {
        $cacheService = $this->getMockBuilder(CacheService::class)
            ->onlyMethods(['setInCaches', 'getFromCaches', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();
        $configurationManager->method('getConfiguration')
            ->willThrowException(new \RuntimeException('dummy', 1700841298));

        $service = new TypoScriptService($cacheService, $configurationManager);
        self::assertSame(null, $service->getTypoScriptByPath('void'));
    }

    public function testGetTypoScriptByPathRethrowsEveryOtherException(): void
    {
        $cacheService = $this->getMockBuilder(CacheService::class)
            ->onlyMethods(['setInCaches', 'getFromCaches', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();
        $configurationManager->method('getConfiguration')
            ->willThrowException(new \RuntimeException('dummy', 1234567890));

        $service = new TypoScriptService($cacheService, $configurationManager);
        self::expectExceptionCode(1234567890);
        $service->getTypoScriptByPath('void');
    }

    /**
     * @test
     */
    public function testGetTypoScriptByPathWhenCacheHasEntry()
    {
        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();
        $cacheService = $this->getMockBuilder(CacheService::class)
            ->onlyMethods(['getFromCaches', 'setInCaches'])
            ->disableOriginalConstructor()
            ->getMock();
        $cacheService->method('getFromCaches')->willReturn(['test_var' => 'test_val']);

        $service = new TypoScriptService($cacheService, $configurationManager);

        $result = $service->getTypoScriptByPath('plugin.tx_test.settings');
        $this->assertEquals(['test_var' => 'test_val'], $result);
    }
}
