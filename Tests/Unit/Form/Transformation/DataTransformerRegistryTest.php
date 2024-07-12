<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Transformation\DataTransformerRegistry;
use FluidTYPO3\Flux\Form\Transformation\Transformer\FloatTransformer;
use FluidTYPO3\Flux\Form\Transformation\Transformer\IntegerTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

class DataTransformerRegistryTest extends TestCase
{
    private DataTransformerRegistry $subject;

    protected function setUp(): void
    {
        $locator = $this->getMockBuilder(ServiceLocator::class)
            ->onlyMethods(['getProvidedServices', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
        $locator->method('getProvidedServices')->willReturn(['a' => true, 'b' => true]);
        $locator->method('get')->willReturnMap(
            [
                ['a', new FloatTransformer()],
                ['b', new IntegerTransformer()],
            ]
        );
        $this->subject = new DataTransformerRegistry($locator);
        parent::setUp();
    }

    public function testResolveDataTransformerByTypeReturnsMatchedType(): void
    {
        self::assertInstanceOf(IntegerTransformer::class, $this->subject->resolveDataTransformerByType('int'));
    }

    public function testResolveDataTransformerByTypeThrowsExceptionForUnmatchedType(): void
    {
        self::expectExceptionCode(1720346755);
        $this->subject->resolveDataTransformerByType('unknown');
    }
}
