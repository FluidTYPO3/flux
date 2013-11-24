<?php
namespace FluidTYPO3\Flux\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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

use \FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class AreaListItemsProcessorTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canGetContentAreasDefinedInUid() {
		$instance = new AreaListItemsProcessor();
		$columns = $instance->getContentAreasDefinedInContentElement(0);
		$this->assertIsArray($columns);
	}

	/**
	 * @test
	 */
	public function canProcessListItems() {
		$instance = new AreaListItemsProcessor();
		$parameters = array(
			'row' => \FluidTYPO3\Flux\Tests\Fixtures\Data\Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$instance->itemsProcFunc($parameters);
	}

	/**
	 * @test
	 */
	public function canGetGridFromProviderAndRecord() {
		$instance = new AreaListItemsProcessor();
		$record = \FluidTYPO3\Flux\Tests\Fixtures\Data\Records::$contentRecordWithoutParentAndWithoutChildren;
		/** @var ProviderInterface $provider */
		$provider = $this->objectManager->get('FluidTYPO3\Flux\Provider\Provider');
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID));
		$grid = $this->callInaccessibleMethod($instance, 'getGridFromConfigurationProviderAndRecord', $provider, $record);
		$this->assertIsArray($grid);
		$this->assertNotEmpty($grid);

	}

	/**
	 * @test
	 */
	public function callsGetContentAreasDefinedInElementIfUrlContainsParent() {
		$class = substr(get_class($this), 0, -4);
		$item1 = array('removable', 'removable');
		$item2 = array('test', 'test');
		$instance = $this->getMock($class, array('getContentAreasDefinedInContentElement', 'getUrlRequestedArea', 'getUrlRequestedParent'));
		$instance->expects($this->once())->method('getUrlRequestedArea')->will($this->returnValue('test'));
		$instance->expects($this->once())->method('getUrlRequestedParent')->will($this->returnValue(1));
		$instance->expects($this->once())->method('getContentAreasDefinedInContentElement')->will($this->returnValue(array($item1, $item2)));
		$parameters = array('items' => array());
		$instance->itemsProcFunc($parameters);
		$this->assertEquals($item2, reset($parameters['items']));
		$this->assertEquals(1, count($parameters['items']));
	}

	/**
	 * @test
	 */
	public function getContentAreasDefinedInElementReturnsEmptyArrayWhenNoProviderIsFound() {
		$class = substr(get_class($this), 0, -4);
		$instance = $this->getMock($class, array('getContentRecordByUid'));
		$instance->expects($this->once())->method('getContentRecordByUid')->with(1)->will($this->returnValue(array()));
		$service = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('resolvePrimaryConfigurationProvider'));
		$service->expects($this->once())->method('resolvePrimaryConfigurationProvider')->will($this->returnValue(NULL));
		ObjectAccess::setProperty($instance, 'fluxService', $service, TRUE);
		$instance->getContentAreasDefinedInContentElement(1);
	}

}
