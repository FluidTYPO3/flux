<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

class CustomViewHelperTest extends AbstractFieldViewHelperTestCase
{
    /**
     * @test
     */
    public function canGenerateAndExecuteClosureWithoutArgumentCollision(): void
    {
        $this->executeViewHelperClosure();
    }

    /**
     * @test
     */
    public function canGenerateAndExecuteClosureWithArgumentCollisionAndBackups(): void
    {
        $arguments = [
            'parameters' => 'Fake parameter'
        ];
        $container = $this->executeViewHelperClosure($arguments);
        $this->assertSame($container->get('parameters'), $arguments['parameters']);
    }

    protected function executeViewHelperClosure(
        array $templateVariableContainerArguments = []
    ): StandardVariableProvider {
        $instance = $this->buildViewHelperInstance();
        $renderingContext = $this->renderingContext;
        $arguments = [
            'name' => 'custom'
        ];
        $this->templateVariableContainer->setSource($templateVariableContainerArguments);
        $node = $this->createViewHelperNode($instance, $arguments);
        $childNode = $this->createNode('Text', 'Hello world!');
        $node->addChildNode($childNode);
        $instance->setViewHelperNode($node);
        /** @var \Closure $closure */
        $closure = $this->callInaccessibleMethod(
            $instance,
            'buildClosure',
            $renderingContext,
            $arguments,
            function () use ($childNode, $renderingContext) {
                return $childNode->evaluate($renderingContext);
            }
        );
        $parameters = [
            'itemFormElName' => 'test',
            'itemFormElLabel' => 'Test label',
        ];
        $output = $closure($parameters);
        $this->assertNotEmpty($output);
        $this->assertSame('Hello world!', $output);
        return $this->templateVariableContainer;
    }
}
