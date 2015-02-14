<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\View\TemplatePaths;
use TYPO3\CMS\Core\Tests\BaseTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TemplatePathsTest
 */
class TemplatePathsTest extends BaseTestCase {

	/**
	 * @dataProvider getGetterAndSetterTestValues
	 * @param string $property
	 * @param mixed $value
	 */
	public function testGetterAndSetter($property, $value) {
		$getter = 'get' . ucfirst($property);
		$setter = 'set' . ucfirst($property);
		$instance = new TemplatePaths();
		$instance->$setter($value);
		$this->assertEquals($value, $instance->$getter());
	}

	/**
	 * @return array
	 */
	public function getGetterAndSetterTestValues() {
		return array(
			array('layoutRootPaths', array('foo' => 'bar')),
			array('templateRootPaths', array('foo' => 'bar')),
			array('partialRootPaths', array('foo' => 'bar'))
		);
	}

	/**
	 * @return void
	 */
	public function testFillByPackageName() {
		$instance = new TemplatePaths('FluidTYPO3.Flux');
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')), $instance->getTemplateRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')), $instance->getLayoutRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')), $instance->getPartialRootPaths());
	}

	/**
	 * @return void
	 */
	public function testFillByLegacyTypoScript() {
		$instance = new TemplatePaths(array(
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates/',
			'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts/',
			'partialRootPath' => 'EXT:flux/Resources/Private/Partials/'
		));
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')), $instance->getTemplateRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')), $instance->getLayoutRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')), $instance->getPartialRootPaths());
	}

	/**
	 * @return void
	 */
	public function testFillByLegacyTypoScriptWithOverlays() {
		$instance = new TemplatePaths(array(
			'overlays' => array(
				'flux' => array(
					'templateRootPath' => 'EXT:flux/Resources/Private/Templates/',
					'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts/',
					'partialRootPath' => 'EXT:flux/Resources/Private/Partials/',
				)
			)
		));
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')), $instance->getTemplateRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')), $instance->getLayoutRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')), $instance->getPartialRootPaths());
	}

	/**
	 * @return void
	 */
	public function testFillByTypoScript() {
		$instance = new TemplatePaths(array(
			'templateRootPaths' => array('EXT:flux/Resources/Private/Templates/'),
			'layoutRootPaths' => array('EXT:flux/Resources/Private/Layouts/'),
			'partialRootPaths' => array('EXT:flux/Resources/Private/Partials/')
		));
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')), $instance->getTemplateRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')), $instance->getLayoutRootPaths());
		$this->assertEquals(array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')), $instance->getPartialRootPaths());
	}

	public function testResolveTemplateFileForControllerAndActionAndFormat() {
		$instance = new TemplatePaths(array(
			'templateRootPaths' => array('EXT:flux/Does/Not/Exist/', 'EXT:flux/Tests/Fixtures/'),
			'layoutRootPaths' => array('EXT:flux/Does/Not/Exist/', 'EXT:flux/Tests/Fixtures/'),
			'partialRootPaths' => array('EXT:flux/Does/Not/Exist/', 'EXT:flux/Tests/Fixtures/')
		));
		// note: this is slight abuse of the method: we aim at the Fixtures directory
		// itself and emulate a controller called "Templates" to make TemplatePaths
		// look in the Fixtures/Templates folder.
		$result = $instance->resolveTemplateFileForControllerAndActionAndFormat('Templates', 'AbsolutelyMinimal');
		$this->assertEquals(ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Templates/AbsolutelyMinimal.html'), $result);
	}

	public function testResolveTemplateFileForControllerAndActionAndFormatReturnsNullIfNotFound() {
		$instance = new TemplatePaths(array(
			'templateRootPaths' => array('EXT:flux/Does/Not/Exist/'),
			'layoutRootPaths' => array('EXT:flux/Does/Not/Exist/'),
			'partialRootPaths' => array('EXT:flux/Does/Not/Exist/')
		));
		// note: this is slight abuse of the method: we aim at the Fixtures directory
		// itself and emulate a controller called "Templates" to make TemplatePaths
		// look in the Fixtures/Templates folder.
		$result = $instance->resolveTemplateFileForControllerAndActionAndFormat('Templates', 'AbsolutelyMinimal');
		$this->assertNull($result);
	}

}
