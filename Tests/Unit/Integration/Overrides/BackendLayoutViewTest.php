<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Overrides\BackendLayoutView;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class ColumnPositionsTest
 */
class BackendLayoutViewTest extends AbstractTestCase
{
    public function testCanSetProvider()
    {
        $instance = new BackendLayoutView();
        $provider = $this->getMockBuilder(GridProviderInterface::class)->getMockForAbstractClass();
        $instance->setProvider($provider);
        $this->assertSame($provider, ObjectAccess::getProperty($instance, 'provider', true));
    }

    public function testCanSetRecord()
    {
        $instance = new BackendLayoutView();
        $record = ['foo' => 'bar'];
        $instance->setRecord($record);
        $this->assertSame($record, ObjectAccess::getProperty($instance, 'record', true));
    }
}
