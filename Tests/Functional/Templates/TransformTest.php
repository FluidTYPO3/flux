<?php
namespace FluidTYPO3\Flux\Tests\Functional\Templates;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * @package Flux
 */
class TransformTest extends AbstractTestCase {

	/**
	 * @return array
	 */
	protected function getTransformedData() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_TRANSFORMATIONS);
		$xml = Xml::EXPECTING_FLUX_TRANSFORMATIONS;
		$service = $this->createFluxServiceInstance();
		$stored = $this->performBasicTemplateReadTest($templatePathAndFilename);
		$data = $service->convertFlexFormContentToArray($xml, $stored);
		return $data;
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithTransformations() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_TRANSFORMATIONS);
		$this->performBasicTemplateReadTest($templatePathAndFilename);
	}

	/**
	 * @test
	 */
	public function returnSameValueOnUnknownTransformation() {
		$data = $this->getTransformedData();
		$this->assertIsArray($data);
		$this->assertSame('0', $data['transform']['unknown']);
	}

	/**
	 * @test
	 */
	public function canTransformStringToArray() {
		$data = $this->getTransformedData();
		$this->assertIsArray($data);
		$this->assertIsArray($data['transform']['stringToArray']);
	}

	/**
	 * @test
	 */
	public function canTransformStringToInteger() {
		$data = $this->getTransformedData();
		$this->assertIsArray($data);
		$this->assertIsInteger($data['transform']['stringToInteger']);
	}

	/**
	 * @test
	 */
	public function canTransformStringToFloat() {
		$data = $this->getTransformedData();
		$this->assertIsArray($data);
		$this->assertSame(1.5, $data['transform']['stringToFloat']);
	}

}
