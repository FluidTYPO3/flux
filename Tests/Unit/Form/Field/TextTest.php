<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FormInterface;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

/**
 * @package Flux
 */
class TextTest extends InputTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array(
		'name' => 'test',
		'label' => 'Test field',
		'enable' => TRUE,
		'maxCharacters' => 30,
		'maximum' => 10,
		'minimum' => 0,
		'validate' => 'trim,int',
		'default' => 'test',
		'columns' => 85,
		'rows' => 8,
		'requestUpdate' => TRUE,
	);

	/**
	 * @test
	 */
	public function canChainSetterForEnableRichText() {
		/** @var Text $instance */
		$instance = $this->createInstance();
		$chained = $instance->setEnableRichText(TRUE);
		$this->assertSame($instance, $chained);
		$this->assertTrue($instance->getEnableRichText());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canChainSetterForDefaultExtras() {
		/** @var Text $instance */
		$instance = $this->createInstance();
		$chained = $instance->setDefaultExtras('void');
		$this->assertSame($instance, $chained);
		$this->assertSame('void', $instance->getDefaultExtras());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canBuildConfigurationWithEnableWithTextWithoutDefaultExtras() {
		/** @var Text $instance */
		$instance = $this->createInstance();
		$instance->setDefaultExtras(NULL)->setEnableRichText(TRUE);
		$this->performTestBuild($instance);
	}

	/**
	 * @return FormInterface
	 */
	protected function createInstance() {
		$instance = parent::createInstance();
		$mockConfigurationManager = $this->getMock(
			'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager',
			array('getConfiguration')
		);
		$mockConfigurationManager->expects($this->any())->method('getConfiguration')->willReturn(array('foo' => 'bar'));
		$instance->injectConfigurationManager($mockConfigurationManager);
		return $instance;
	}

}
