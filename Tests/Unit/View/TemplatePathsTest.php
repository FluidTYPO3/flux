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
		return [
			['layoutRootPaths', ['foo' => 'bar']],
			['templateRootPaths', ['foo' => 'bar']],
			['partialRootPaths', ['foo' => 'bar']]
		];
	}

	/**
	 * @return void
	 */
	public function testFillByPackageName() {
		$instance = new TemplatePaths('FluidTYPO3.Flux');
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')], $instance->getTemplateRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')], $instance->getLayoutRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')], $instance->getPartialRootPaths());
	}

	/**
	 * @return void
	 */
	public function testFillByLegacyTypoScript() {
		$instance = new TemplatePaths([
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates/',
			'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts/',
			'partialRootPath' => 'EXT:flux/Resources/Private/Partials/'
		]);
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')], $instance->getTemplateRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')], $instance->getLayoutRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')], $instance->getPartialRootPaths());
	}

	/**
	 * @return void
	 */
	public function testFillByLegacyTypoScriptWithOverlays() {
		$instance = new TemplatePaths([
			'overlays' => [
				'flux' => [
					'templateRootPath' => 'EXT:flux/Resources/Private/Templates/',
					'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts/',
					'partialRootPath' => 'EXT:flux/Resources/Private/Partials/',
				]
			]
		]);
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')], $instance->getTemplateRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')], $instance->getLayoutRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')], $instance->getPartialRootPaths());
	}

	/**
	 * @return void
	 */
	public function testFillByTypoScript() {
		$instance = new TemplatePaths([
			'templateRootPaths' => ['EXT:flux/Resources/Private/Templates/'],
			'layoutRootPaths' => ['EXT:flux/Resources/Private/Layouts/'],
			'partialRootPaths' => ['EXT:flux/Resources/Private/Partials/']
		]);
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')], $instance->getTemplateRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')], $instance->getLayoutRootPaths());
		$this->assertEquals([ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')], $instance->getPartialRootPaths());
	}

	public function testResolveTemplateFileForControllerAndActionAndFormat() {
		$instance = new TemplatePaths([
			'templateRootPaths' => ['EXT:flux/Does/Not/Exist/', 'EXT:flux/Tests/Fixtures/'],
			'layoutRootPaths' => ['EXT:flux/Does/Not/Exist/', 'EXT:flux/Tests/Fixtures/'],
			'partialRootPaths' => ['EXT:flux/Does/Not/Exist/', 'EXT:flux/Tests/Fixtures/']
		]);
		// note: this is slight abuse of the method: we aim at the Fixtures directory
		// itself and emulate a controller called "Templates" to make TemplatePaths
		// look in the Fixtures/Templates folder.
		$result = $instance->resolveTemplateFileForControllerAndActionAndFormat('Templates', 'AbsolutelyMinimal');
		$this->assertEquals(ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Templates/AbsolutelyMinimal.html'), $result);
	}

	public function testResolveTemplateFileForControllerAndActionAndFormatReturnsNullIfNotFound() {
		$instance = new TemplatePaths([
			'templateRootPaths' => ['EXT:flux/Does/Not/Exist/'],
			'layoutRootPaths' => ['EXT:flux/Does/Not/Exist/'],
			'partialRootPaths' => ['EXT:flux/Does/Not/Exist/']
		]);
		// note: this is slight abuse of the method: we aim at the Fixtures directory
		// itself and emulate a controller called "Templates" to make TemplatePaths
		// look in the Fixtures/Templates folder.
		$result = $instance->resolveTemplateFileForControllerAndActionAndFormat('Templates', 'AbsolutelyMinimal');
		$this->assertNull($result);
	}

	/**
	 * @dataProvider getResolveFilesMethodTestValues
	 * @param string $method
	 */
	public function testResolveFilesMethodCallsResolveFilesInFolders($method, $pathsMethod) {
		$instance = $this->getMock('FluidTYPO3\\Flux\\View\\TemplatePaths', ['resolveFilesInFolders']);
		$instance->$pathsMethod(['foo']);
		$instance->expects($this->once())->method('resolveFilesInFolders')->with($this->anything(), 'format');
		$instance->$method('format', 'format');
	}

	/**
	 * @return array
	 */
	public function getResolveFilesMethodTestValues() {
		return [
			['resolveAvailableTemplateFiles', 'setTemplateRootPaths'],
			['resolveAvailablePartialFiles', 'setPartialRootPaths'],
			['resolveAvailableLayoutFiles', 'setLayoutRootPaths']
		];
	}

	/**
	 * @return void
	 */
	public function testResolveFilesInFolders() {
		$instance = new TemplatePaths();
		$folder = GeneralUtility::getFileAbsFileName('EXT:flux/Tests/Fixtures/Partials/');
		$file = GeneralUtility::getFileAbsFileName('EXT:flux/Tests/Fixtures/Partials/FormComponents.html');
		$files = $this->callInaccessibleMethod($instance, 'resolveFilesInFolders', [$folder], TemplatePaths::DEFAULT_FORMAT);
		$this->assertEquals([$file], $files);
	}

	/**
	 * @return void
	 */
	public function testToArray() {
		$instance = new TemplatePaths();
		$instance->setTemplateRootPaths(['1']);
		$instance->setLayoutRootPaths(['2']);
		$instance->setPartialRootPaths(['3']);
		$result = $instance->toArray();
		$expected = [
			TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [1],
			TemplatePaths::CONFIG_LAYOUTROOTPATHS => [2],
			TemplatePaths::CONFIG_PARTIALROOTPATHS => [3]
		];
		$this->assertEquals($expected, $result);
	}

}
