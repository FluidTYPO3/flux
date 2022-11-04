<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\TypeConverterPipe;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\Pipe\AbstractPipeViewHelper;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter;

/**
 * TypeConverterViewHelperTest
 */
class TypeConverterViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @dataProvider getTestArguments
     * @param array $arguments
     */
    public function testWithArguments(array $arguments)
    {
        $result = $this->executeViewHelper($arguments, array(), null, null, 'FakePlugin');
        $this->assertSame('', $result);
    }

    /**
     * @return array
     */
    public function getTestArguments()
    {
        return array(
            array(array('typeConverter' => 'Array')),
            array(array('typeConverter' => ArrayConverter::class)),
            array(array('typeConverter' => new ArrayConverter())),
            array(array('typeConverter' => ArrayConverter::class, 'direction' => AbstractPipeViewHelper::DIRECTION_IN)),
        );
    }

    protected function createObjectManagerInstance(): ObjectManagerInterface
    {
        $objectManager = parent::createObjectManagerInstance();
        $objectManager->method('get')->willReturnMap(
            [
                [TypeConverterPipe::class, new TypeConverterPipe()],
                [ArrayConverter::class, new ArrayConverter()],
            ]
        );
        return $objectManager;
    }
}
