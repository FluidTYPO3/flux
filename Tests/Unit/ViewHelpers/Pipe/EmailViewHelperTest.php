<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

/**
 * @package Flux
 */
class EmailViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @dataProvider getTestArguments
	 * @param array $arguments
	 */
	public function testWithArguments(array $arguments) {
		$result = $this->executeViewHelper($arguments, [], NULL, NULL, 'FakePlugin');
		$this->assertNull($result);
	}

	/**
	 * @return array
	 */
	public function getTestArguments() {
		return [
			[[]],
		];
	}

}
