<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\AbstractProvider;

class DummyConfigurationProvider extends AbstractProvider
{
    protected ?string $tableName = 'test';
    protected string $extensionKey = 'test';
    protected ?string $fieldName = 'test';
    protected ?string $templatePathAndFilename = 'EXT:flux/Tests/Fixtures/Templates/DummyConfigurationProvider.html';
}
