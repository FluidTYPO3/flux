<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\PageLanguageOverlayProvider;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class PageLanguageOverlayProviderTest extends AbstractTestCase
{
    public function testLoadRecordTreeFromDatabase(): void
    {
        $record = [
            'uid' => 10,
            'sys_language_uid' => 15,
        ];
        $pageRecord1 = [
            'uid' => 123,
            'pid' => 1,
        ];
        $pageRecord2 = [
            'uid' => 1,
            'pid' => 12,
        ];
        $parentTranslation1 = [
            'uid' => 124,
            'pid' => 2,
            'sys_language_uid' => 15,
        ];

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $recordService->method('getSingle')->willReturnMap(
            [
                ['pages_language_overlay', 'pid', 10, $record + ['pid' => 123]],
                ['pages', '*', 123, $pageRecord1],
                ['pages', '*', 1, $pageRecord2],
            ]
        );
        $recordService->method('get')->willReturnOnConsecutiveCalls([$parentTranslation1], []);

        $instance = new PageLanguageOverlayProvider();
        $instance->injectRecordService($recordService);

        $output = $this->callInaccessibleMethod($instance, 'loadRecordTreeFromDatabase', $record);
        self::assertSame([$parentTranslation1], $output);
    }

    public function testLoadRecordTreeFromDatabaseReturnsEmptyArrayIfPageRecordNotFound(): void
    {
        $record = [
            'uid' => 10,
        ];

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle'])
            ->disableOriginalConstructor()
            ->getMock();
        $recordService->method('getSingle')->willReturnMap(
            [
                ['pages_language_overlay', 'pid', 10, $record + ['pid' => 123]],
                ['pages', '*', 123, null],
            ]
        );

        $instance = new PageLanguageOverlayProvider();
        $instance->injectRecordService($recordService);

        $output = $this->callInaccessibleMethod($instance, 'loadRecordTreeFromDatabase', $record);
        self::assertSame([], $output);
    }

    public function testGetControllerActionReferenceFromRecord(): void
    {
        $record = [
            'uid' => 1,
            'pid' => 2,
        ];

        $pageRecord = [
            'uid' => 123,
            'pid' => 1,
            PageProvider::FIELD_ACTION_MAIN => [
                'ext->action',
            ]
        ];

        $pageService = $this->getMockBuilder(PageService::class)
            ->setMethods(['getPageTemplateConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageService->method('getPageTemplateConfiguration')->willReturn(
            [
                PageProvider::FIELD_ACTION_MAIN => 'ext->action',
                PageProvider::FIELD_ACTION_SUB => 'ext->sub',
            ]
        );

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $recordService->method('getSingle')->willReturn($pageRecord);

        $instance = new PageLanguageOverlayProvider();
        $instance->injectRecordService($recordService);
        $instance->injectPageService($pageService);

        $output = $instance->getControllerActionReferenceFromRecord($record);
        self::assertSame('ext->action', $output);
    }
}
