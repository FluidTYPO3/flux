<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeFluxTemplateDumper;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form\Conversion\FormToFluidTemplateConverter;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\TemplateValidationService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyContentTypeManager;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

class ContentTypeFluxTemplateDumperTest extends AbstractTestCase
{
    protected ?array $record = null;
    protected ContentTypeDefinitionInterface $contentTypeDefinition;
    protected ContentTypeManager $contentTypeManager;
    protected CacheService $cacheService;
    protected TemplateValidationService $validationService;
    protected TemplateView $templateView;
    protected ContentTypeFluxTemplateDumper $subject;

    protected function setUp(): void
    {
        $this->record = [
            'uid' => 123,
            'title' => 'Test form',
            'description' => 'Test form',
            'icon' => 'test',
            'content_type' => 'flux_test',
            'sorting' => 123,
        ];
        $this->contentTypeDefinition = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->onlyMethods(['getContentConfiguration', 'getGridConfiguration', 'getTemplateSource'])
            ->setConstructorArgs([$this->record])
            ->getMock();
        $this->contentTypeDefinition->method('getContentConfiguration')->willReturn([]);
        $this->contentTypeDefinition->method('getGridConfiguration')->willReturn([]);

        $this->cacheService = $this->getMockBuilder(CacheService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setInCaches', 'getFromCaches', 'remove'])
            ->getMock();
        $this->cacheService->method('getFromCaches')->willReturn([$this->contentTypeDefinition]);

        $this->contentTypeManager = new ContentTypeManager($this->cacheService);

        $this->contentTypeManager->registerTypeDefinition($this->contentTypeDefinition);

        $this->validationService = $this->getMockBuilder(TemplateValidationService::class)
            ->onlyMethods(['validateTemplateSource'])
            ->disableOriginalConstructor()
            ->getMock();

        $templateParser = new TemplateParser();

        $renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->onlyMethods(
                [
                    'getViewHelperVariableContainer',
                    'getViewHelperResolver',
                    'getTemplateParser',
                    'getVariableProvider',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $renderingContext->method('getViewHelperVariableContainer')->willReturn(new ViewHelperVariableContainer());
        $renderingContext->method('getViewHelperResolver')->willReturn(new ViewHelperResolver());
        $renderingContext->method('getVariableProvider')->willReturn(new StandardVariableProvider());
        $renderingContext->method('getTemplateParser')->willReturn($templateParser);

        $templateParser->setRenderingContext($renderingContext);

        $this->templateView = new TemplateView($renderingContext);
        $this->templateView->getRenderingContext()
            ->getViewHelperResolver()
            ->addNamespace('flux', 'FluidTYPO3\\Flux\\ViewHelpers');

        $this->subject = new ContentTypeFluxTemplateDumper(
            new FormToFluidTemplateConverter(),
            $this->contentTypeManager,
            $this->validationService
        );

        parent::setUp();
    }

    public function testDumpTemplateFromRecordReturnsEmptyStringOnMissingContentTypeDefinition(): void
    {
        self::assertSame('', $this->subject->dumpFluxTemplate(['row' => ['uid' => 123, 'content_type' => '']]));
    }

    public function testDumpTemplateFromRecordReturnsEmptyStringOnNewRecord(): void
    {
        self::assertSame('', $this->subject->dumpFluxTemplate(['row' => ['uid' => 'NEW123']]));
    }

    public function testDumpTemplateFromRecordBasedContentTypeDefinition(): void
    {
        GeneralUtility::addInstance(TemplateView::class, $this->templateView);

        $this->contentTypeDefinition->method('getTemplateSource')->willReturn('');

        $parameters = [
            'row' => $this->record,
        ];

        $output = $this->subject->dumpFluxTemplate($parameters);
        $expected = <<< SOURCE
<p class="text-success">Template parses OK, it is safe to copy</p><pre>&lt;f:layout /&gt;
&lt;f:section name=&quot;Configuration&quot;&gt;
    &lt;flux:form id=&quot;&quot;&gt;
        &lt;!-- Generated by EXT:flux from runtime configured content type --&gt;

    &lt;/flux:form&gt;
    &lt;flux:grid&gt;
        &lt;!-- Generated by EXT:flux from runtime configured content type --&gt;

    &lt;/flux:grid&gt;
&lt;/f:section&gt;

&lt;f:section name=&quot;Main&quot;&gt;

&lt;/f:section&gt;</pre>
SOURCE;

        self::assertSame($expected, $output);
    }

    public function testDumpTemplateRendersErrorIfTemplateParsingCausesError(): void
    {
        $this->contentTypeDefinition->method('getTemplateSource')->willReturn('<f:invalid');
        $this->validationService->method('validateTemplateSource')->willReturn('test error');

        $parameters = [
            'row' => $this->record,
        ];

        $output = $this->subject->dumpFluxTemplate($parameters);
        $expected = <<< SOURCE
<p class="text-danger">test error</p><pre>&lt;f:layout /&gt;
&lt;f:section name=&quot;Configuration&quot;&gt;
    &lt;flux:form id=&quot;&quot;&gt;
        &lt;!-- Generated by EXT:flux from runtime configured content type --&gt;

    &lt;/flux:form&gt;
    &lt;flux:grid&gt;
        &lt;!-- Generated by EXT:flux from runtime configured content type --&gt;

    &lt;/flux:grid&gt;
&lt;/f:section&gt;

&lt;f:section name=&quot;Main&quot;&gt;
&lt;f:invalid
&lt;/f:section&gt;</pre>
SOURCE;

        self::assertSame($expected, $output);
    }
}
