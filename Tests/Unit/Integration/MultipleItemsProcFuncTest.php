<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\MultipleItemsProcFunc;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\DataHandling\ItemProcessingService;

class MultipleItemsProcFuncTest extends AbstractTestCase
{
    private static $executed = false;

    public function testRegistersFunction(): void
    {
        MultipleItemsProcFunc::register('table', 'field', static::class . '->dummyFunction');
        self::assertSame(
            MultipleItemsProcFunc::class . '->execute',
            $GLOBALS['TCA']['table']['columns']['field']['config']['itemsProcFunc']
        );
        self::assertSame(
            [static::class . '->dummyFunction'],
            $GLOBALS['TCA']['table']['multipleItemsProcessingFunctions']['field']
        );
    }

    public function testExecutesFunctionWithFormDataProvider(): void
    {
        static::$executed = false;
        $formDataProviderInterface = $this->getMockBuilder(FormDataProviderInterface::class)->getMockForAbstractClass();
        MultipleItemsProcFunc::register('table', 'field', static::class . '->dummyFunction');
        $parameters = ['table' => 'table', 'field' => 'field'];
        (new MultipleItemsProcFunc())->execute($parameters, $formDataProviderInterface);

        self::assertTrue(static::$executed);
    }

    public function testExecutesFunctionWithItemProcessingService(): void
    {
        static::$executed = false;
        if (!class_exists(ItemProcessingService::class)) {
            $this->markTestSkipped('Skippped, class ' . ItemProcessingService::class . ' does not exist');
        }
        MultipleItemsProcFunc::register('table', 'field', static::class . '->dummyFunction');
        $parameters = ['table' => 'table', 'field' => 'field'];
        $itemProcessingServiceProvider = $this->getMockBuilder(ItemProcessingService::class)->getMockForAbstractClass();
        (new MultipleItemsProcFunc())->execute($parameters, $itemProcessingServiceProvider
                                               
        self::assertTrue(static::$executed);
    }

    public function dummyFunction(): void
    {
        static::$executed = true;
    }
}
