<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\FluidFileBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class DropInContentTypeDefinitionTest extends AbstractTestCase
{
    public function testConstructingInstanceFillsExpectedProperties(): void
    {
        $relativePath = 'Tests/Fixtures/Templates/Content/AbsolutelyMinimal.html';
        $path = realpath('.');
        $subject = new DropInContentTypeDefinition(
            'FluidTYPO3.Flux',
            $path,
            $relativePath,
            Provider::class
        );

        self::assertSame('FluidTYPO3.Flux', $subject->getExtensionIdentity(), 'Extension identity is unexpected value');
        self::assertSame(Provider::class, $subject->getProviderClassName(), 'Provider class name is unexpected value');
        self::assertSame('', $subject->getIconReference(), 'Icon reference is unexpected value');
    }
}
