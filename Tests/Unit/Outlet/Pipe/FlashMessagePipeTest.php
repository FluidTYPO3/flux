<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe\AbstractPipeTestCase;

/**
 * FlashMessagePipeTest
 */
class FlashMessagePipeTest extends AbstractPipeTestCase {

	/**
	 * @var array
	 */
	protected $defaultData = array(
		'severity' => 0,
		'title' => 'test',
		'message' => 'test2',
		'storeInSession' => FALSE
	);

	/**
	 * @return void
	 */
	public function setUp() {
		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		unset($GLOBALS['BE_USER']);
	}

	/**
	 * @test
	 */
	public function canGetAndSetSeverity() {
		$this->assertGetterAndSetterWorks('severity', 4, 4, TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetTitle() {
		$this->assertGetterAndSetterWorks('title', 'test', 'test', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetMessage() {
		$this->assertGetterAndSetterWorks('message', 'test', 'test', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetStoreInSession() {
		$this->assertGetterAndSetterWorks('storeInSession', TRUE, TRUE, TRUE);
	}

}
