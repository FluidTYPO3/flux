<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Tests\BaseTestCase;

/**
 * Class ViewContextTest
 */
class ViewContextTest extends BaseTestCase {

	/**
	 * @dataProvider getGetterAndSetterTestValues
	 * @param string $property
	 * @param mixed $value
	 */
	public function testGetterAndSetter($property, $value) {
		$getter = 'get' . ucfirst($property);
		$setter = 'set' . ucfirst($property);
		$instance = new ViewContext();
		$instance->$setter($value);
		$this->assertEquals($value, $instance->$getter());
	}

	/**
	 * @return array
	 */
	public function getGetterAndSetterTestValues() {
		return [
			['sectionName', 'Configuration'],
			['packageName', 'Package'],
			['variables', ['foo' =>'bar']],
			['templatePaths', new TemplatePaths('Flux')],
			['controllerName', 'Controller'],
			['templatePathAndFilename', 'filename'],
			['format', 'xml']
		];
	}

}
