<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Service\TemplateValidationService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\TemplatePaths;

class TemplateValidationServiceTest extends AbstractTestCase
{
    private RecordBasedContentTypeDefinition $definition;
    private TemplateView $view;
    private TemplateParser $parser;
    private RenderingContextInterface $renderingContext;
    private TemplateValidationService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->definition = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->onlyMethods(['getTemplateSource'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->definition->method('getTemplateSource')->willReturn('foo');

        $this->parser = $this->getMockBuilder(TemplateParser::class)
            ->onlyMethods(['parse'])
            ->disableOriginalConstructor()
            ->getMock();

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->onlyMethods(['fillDefaultsByPackageName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->renderingContext = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $this->renderingContext->method('getTemplatePaths')->willReturn($templatePaths);
        $this->renderingContext->method('getTemplateParser')->willReturn($this->parser);

        $this->view = $this->getMockBuilder(TemplateView::class)
            ->onlyMethods(['getRenderingContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->view->method('getRenderingContext')->willReturn($this->renderingContext);

        $this->subject = new TemplateValidationService();
    }

    public function testValidateContentDefinitionReturnsNullOnNonFluidContentDefinition(): void
    {
        $definition = $this->getMockBuilder(ContentTypeDefinitionInterface::class)->getMockForAbstractClass();
        self::assertSame(null, $this->subject->validateContentDefinition($definition));
    }

    public function testValidateContentDefinitionReturnsExceptionMessageFromParser(): void
    {
        GeneralUtility::addInstance(TemplateView::class, $this->view);
        $this->parser->method('parse')->willThrowException(new Exception('error'));
        self::assertSame('error', $this->subject->validateContentDefinition($this->definition));
    }

    public function testValidateContentDefinition(): void
    {
        GeneralUtility::addInstance(TemplateView::class, $this->view);
        self::assertSame(null, $this->subject->validateContentDefinition($this->definition));
    }
}
