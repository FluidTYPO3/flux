<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\AbstractProvider;

/**
 * DummyConfigurationProvider
 */
class DummyConfigurationProvider extends AbstractProvider {

	/**
	 * @var string
	 */
	protected $tableName = 'test';

	/**
	 * @var string
	 */
	protected $extensionKey = 'test';

	/**
	 * @var string
	 */
	protected $fieldName = 'test';

	/**
	 * @var string
	 */
	protected $templatePathAndFilename = 'EXT:flux/Tests/Fixtures/Templates/DummyConfigurationProvider.html';

}
