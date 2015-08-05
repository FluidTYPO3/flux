<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field\AbstractFieldViewHelperTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;

/**
 * @package Flux
 */
class CustomViewHelperTest extends AbstractFieldViewHelperTestCase {

	/**
	 * @test
	 */
	public function canGenerateAndExecuteClosureWithoutArgumentCollision() {
		$this->executeViewHelperClosure();
	}

	/**
	 * @test
	 */
	public function canGenerateAndExecuteClosureWithArgumentCollisionAndBackups() {
		$arguments = [
			'parameters' => 'Fake parameter'
		];
		$container = $this->executeViewHelperClosure($arguments);
		$this->assertSame($container->get('parameters'), $arguments['parameters']);
	}

	/**
	 * @param array $templateVariableContainerArguments
	 * @return TemplateVariableContainer
	 */
	protected function executeViewHelperClosure($templateVariableContainerArguments = []) {
		$instance = $this->objectManager->get('FluidTYPO3\Flux\ViewHelpers\Field\CustomViewHelper');
		$renderingContext = $this->objectManager->get('TYPO3\CMS\Fluid\Core\Rendering\RenderingContext');
		$container = $renderingContext->getTemplateVariableContainer();
		$arguments = [
			'name' => 'custom'
		];
		foreach ($templateVariableContainerArguments as $name => $value) {
			$container->add($name, $value);
		}
		$node = new ViewHelperNode($instance, $arguments);
		$childNode = new TextNode('Hello world!');
		$node->addChildNode($childNode);
		$instance->setRenderingContext($renderingContext);
		$instance->setViewHelperNode($node);
		/** @var \Closure $closure */
		$closure = $this->callInaccessibleMethod($instance, 'buildClosure');
		$parameters = [
			'itemFormElName' => 'test',
			'itemFormElLabel' => 'Test label',
		];
		$output = $closure($parameters);
		$this->assertNotEmpty($output);
		$this->assertSame('Hello world!', $output);
		return $instance->getTemplateVariableContainer();
	}

}
