<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Integration\FormEngine\ProviderProcessor;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

class ProviderProcessorTest extends AbstractTestCase
{
    protected ProviderProcessor $subject;

    public function testThrowsExceptionOnMissingTableName(): void
    {
        $subject = new ProviderProcessor(
            $this->getMockBuilder(ProviderResolver::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContentTypeManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SiteFinder::class)->disableOriginalConstructor()->getMock()
        );
        self::expectExceptionCode(1666816552);
        $subject->addData([]);
    }

    public function testThrowsExceptionOnMissingDatabaseRow(): void
    {
        $subject = new ProviderProcessor(
            $this->getMockBuilder(ProviderResolver::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContentTypeManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SiteFinder::class)->disableOriginalConstructor()->getMock()
        );
        self::expectExceptionCode(1666816552);
        $subject->addData(['tableName' => 'test']);
    }

    public function testCallsProcessTableConfigurationOnProviders(): void
    {
        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['processTableConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())->method('processTableConfiguration')->willReturn([]);

        $providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->setMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();
        $providerResolver->expects($this->once())->method('resolveConfigurationProviders')->willReturn([$provider]);

        $instance = new ProviderProcessor(
            $providerResolver,
            $this->getMockBuilder(ContentTypeManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SiteFinder::class)->disableOriginalConstructor()->getMock()
        );

        $result = $instance->addData(['tableName' => 'foo', 'databaseRow' => []]);
        $this->assertEquals([], $result);
    }

    public function testSetsEnabledContentTypesFromSiteConfiguration(): void
    {
        $site = $this->getMockBuilder(Site::class)
            ->setMethods(['getConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $site->method('getConfiguration')->willReturn(['flux_content_types' => 'flux_test,flux_test2']);

        $siteFinder = $this->getMockBuilder(SiteFinder::class)
            ->setMethods(['getSiteByPageId'])
            ->disableOriginalConstructor()
            ->getMock();
        $siteFinder->method('getSiteByPageId')->willReturn($site);

        $contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['fetchContentTypeNames'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeManager->method('fetchContentTypeNames')
            ->willReturn(['flux_test', 'flux_test2', 'flux_notincluded']);

        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['processTableConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())->method('processTableConfiguration')->willReturnArgument(1);

        $providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->setMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();
        $providerResolver->expects($this->once())->method('resolveConfigurationProviders')->willReturn([$provider]);

        $instance = new ProviderProcessor(
            $providerResolver,
            $contentTypeManager,
            $siteFinder
        );

        $input = [
            'tableName' => 'tt_content',
            'parentPageRow' => ['uid' => 123],
            'databaseRow' => [],
            'processedTca' => [
                'columns' => [
                    'CType' => [
                        'config' => [
                            'items' => [
                                [
                                    'foo',
                                    'flux_notincluded',
                                    'foo',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $expected = $input;
        unset($expected['processedTca']['columns']['CType']['config']['items'][0]);

        $result = $instance->addData($input);
        $this->assertEquals($expected, $result);
    }

    public function testSetsEnabledContentTypesFromSiteConfigurationButIgnoresSiteNotFound(): void
    {
        $site = $this->getMockBuilder(Site::class)
            ->setMethods(['getConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $site->method('getConfiguration')->willReturn(['flux_content_types' => 'flux_test,flux_test2']);

        $siteFinder = $this->getMockBuilder(SiteFinder::class)
            ->setMethods(['getSiteByPageId'])
            ->disableOriginalConstructor()
            ->getMock();
        $siteFinder->method('getSiteByPageId')->willThrowException(new SiteNotFoundException('test'));

        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['processTableConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())->method('processTableConfiguration')->willReturnArgument(1);

        $providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->setMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();
        $providerResolver->expects($this->once())->method('resolveConfigurationProviders')->willReturn([$provider]);

        $instance = new ProviderProcessor(
            $providerResolver,
            $this->getMockBuilder(ContentTypeManager::class)->disableOriginalConstructor()->getMock(),
            $siteFinder
        );

        $input = [
            'tableName' => 'tt_content',
            'parentPageRow' => ['uid' => 123],
            'databaseRow' => [],
            'processedTca' => [
                'columns' => [
                    'CType' => [
                        'config' => [
                            'items' => [
                                [
                                    'foo',
                                    'flux_notincluded',
                                    'foo',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $expected = $input;

        $result = $instance->addData($input);
        $this->assertEquals($expected, $result);
    }
}
