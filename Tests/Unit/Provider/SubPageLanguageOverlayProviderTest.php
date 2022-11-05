<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\SubPageLanguageOverlayProvider;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class SubPageLanguageOverlayProviderTest extends AbstractTestCase
{
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

        $instance = new SubPageLanguageOverlayProvider();
        $instance->injectRecordService($recordService);
        $instance->injectPageService($pageService);

        $output = $instance->getControllerActionReferenceFromRecord($record);
        self::assertSame('ext->sub', $output);
    }

    public function testGetControllerActionReferenceFromRecordReturnsNullIfPageRecordNotFound(): void
    {
        $record = [
            'uid' => 1,
            'pid' => 2,
        ];

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $recordService->method('getSingle')->willReturn(null);

        $instance = new SubPageLanguageOverlayProvider();
        $instance->injectRecordService($recordService);

        $output = $instance->getControllerActionReferenceFromRecord($record);
        self::assertSame(null, $output);
    }
}
