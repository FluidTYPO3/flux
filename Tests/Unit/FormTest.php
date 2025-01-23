<?php
namespace FluidTYPO3\Flux\Tests\Unit;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Outlet\StandardOutlet;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * FormTest
 */
class FormTest extends AbstractTestCase
{
    /**
     * @return Form
     */
    protected function getEmptyDummyForm()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        return $form;
    }

    /**
     * @param string $template
     * @return Form
     */
    protected function getDummyFormFromTemplate($template = self::FIXTURE_TEMPLATE_BASICGRID)
    {
        $templateCompiler = $this->getMockBuilder(TemplateCompiler::class)->getMock();
        $templateParser = new TemplateParser();
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $variableProvider = new StandardVariableProvider();
        $viewHelperResolver = new ViewHelperResolver();
        $viewHelperInvoker = new ViewHelperInvoker();
        $namespaceDetectionTemplateProcessor = new NamespaceDetectionTemplateProcessor();
        $templatePaths = new TemplatePaths();
        $templatePaths->setTemplateRootPaths(['Tests/Fixtures/Templates/']);
        $templatePaths->setPartialRootPaths(['Tests/Fixtures/Partials/']);
        $templatePaths->setLayoutRootPaths(['Tests/Fixtures/Layouts/']);
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['getControllerExtensionName'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('getControllerExtensionName')->willReturn('Flux');

        $renderingContext = $this->getMockBuilder(RenderingContext::class)->setMethods(
            [
                'getTemplatePaths',
                'getViewHelperVariableContainer',
                'getVariableProvider',
                'getTemplateCompiler',
                'getViewHelperInvoker',
                'getTemplateParser',
                'getViewHelperResolver',
                'getTemplateProcessors',
                'getExpressionNodeTypes',
                'getControllerName',
                'getControllerAction',
                'getControllerContext',
            ]
        )->disableOriginalConstructor()->getMock();
        $renderingContext->method('getTemplatePaths')->willReturn($templatePaths);
        $renderingContext->method('getViewHelperVariableContainer')->willReturn($viewHelperVariableContainer);
        $renderingContext->method('getVariableProvider')->willReturn($variableProvider);
        $renderingContext->method('getTemplateCompiler')->willReturn($templateCompiler);
        $renderingContext->method('getTemplateParser')->willReturn($templateParser);
        $renderingContext->method('getViewHelperInvoker')->willReturn($viewHelperInvoker);
        $renderingContext->method('getViewHelperResolver')->willReturn($viewHelperResolver);
        $renderingContext->method('getTemplateProcessors')->willReturn([$namespaceDetectionTemplateProcessor]);
        $renderingContext->method('getExpressionNodeTypes')->willReturn([]);
        $renderingContext->method('getControllerName')->willReturn('Content');
        $renderingContext->method('getControllerAction')->willReturn(basename($template, '.html'));
        if (class_exists(ControllerContext::class)) {
            $controllerContext = new ControllerContext();
            $controllerContext->setRequest($request);
            $renderingContext->method('getControllerContext')->willReturn($controllerContext);
        }

        $namespaceDetectionTemplateProcessor->setRenderingContext($renderingContext);

        $templateParser->setRenderingContext($renderingContext);

        $view = new TemplateView($renderingContext);

        $view->renderSection('Configuration', [], true);
        return $view->getRenderingContext()->getViewHelperVariableContainer()->get(FormViewHelper::class, 'form');
    }

    /**
     * @test
     */
    public function canReturnFormObjectWithoutFormPresentInTemplate()
    {
        $form = $this->getDummyFormFromTemplate(self::FIXTURE_TEMPLATE_WITHOUTFORM);
        $this->assertIsValidAndWorkingFormObject($form);
    }

    /**
     * @test
     */
    public function canRetrieveStoredForm()
    {
        $form = $this->getDummyFormFromTemplate();
        $this->assertIsValidAndWorkingFormObject($form);
    }

    /**
     * @test
     */
    public function canUseIdProperty()
    {
        $form = $this->getDummyFormFromTemplate();
        $id = 'dummyId';
        $form->setId($id);
        $this->assertSame($id, $form->getId());
    }

    /**
     * @test
     */
    public function canUseEnabledProperty()
    {
        $form = $this->getDummyFormFromTemplate();
        $form->setEnabled(false);
        $this->assertSame(false, $form->getEnabled());
    }

    /**
     * @test
     */
    public function canUseExtensionNameProperty()
    {
        $form = $this->getDummyFormFromTemplate();
        $extensionName = 'flux';
        $form->setExtensionName($extensionName);
        $this->assertSame($extensionName, $form->getExtensionName());
    }

    /**
     * @test
     */
    public function canUseDescriptionProperty()
    {
        $form = $this->getDummyFormFromTemplate();
        $description = 'This is a dummy description';
        $form->setDescription($description);
        $this->assertSame($description, $form->getDescription());
    }

    /**
     * @test
     */
    public function canUseDescriptionPropertyAndReturnLanguageLabelWhenDescriptionEmpty()
    {
        $form = $this->getDummyFormFromTemplate();
        $form->setDescription(null);
        $this->assertNotNull($form->getDescription());
    }

    /**
     * @test
     */
    public function canAddSameFieldTwiceWithoutErrorAndWithoutDoubles()
    {
        $form = $this->getEmptyDummyForm();
        $field = $form->createField('Input', 'input', 'Input field');
        $form->last()->add($field)->add($field);
        $this->assertTrue($form->last()->has($field));
    }

    /**
     * @test
     */
    public function canAddSameContainerTwiceWithoutErrorAndWithoutDoubles()
    {
        $form = $this->getEmptyDummyForm();
        $sheet = $form->createContainer('Sheet', 'sheet', 'Sheet object');
        $form->add($sheet)->add($sheet);
        $this->assertTrue($form->has($sheet));
    }

    /**
     * @test
     */
    public function canGetLabelFromVariousObjectsInsideForm()
    {
        $form = $this->getEmptyDummyForm();
        $field = $form->createField('Input', 'test');
        $objectField = $form->createField('Input', 'objectField');
        $form->add($field);
        $section = $form->createContainer('Section', 'section');
        $object = $form->createContainer('SectionObject', 'object');
        $object->add($objectField);
        $section->add($object);
        $form->add($section);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $object->getRoot());
        $this->assertNotEmpty($form->get('options')->getLabel());
        $this->assertNotEmpty($form->get('test', true)->getLabel());
        $this->assertNotEmpty($form->get('object', true)->getLabel());
        $this->assertNotEmpty($form->get('objectField', true)->getLabel());
    }

    /**
     * @test
     */
    public function canAddMultipleFieldsToContainer()
    {
        $form = $this->getEmptyDummyForm();
        $fields = [
            $form->createField('Input', 'test1'),
            $form->createField('Input', 'test2'),
        ];
        $form->addAll($fields);
        $this->assertTrue($form->last()->has($fields[0]));
        $this->assertTrue($form->last()->has($fields[1]));
    }

    /**
     * @test
     */
    public function canRemoveFieldFromContainerByName()
    {
        $form = $this->getEmptyDummyForm();
        $field = $form->createField('Input', 'test');
        $form->add($field);
        $form->last()->remove('test');
        $this->assertFalse($form->last()->has('test'));
    }

    /**
     * @test
     */
    public function canRemoveFieldFromContainerByInstance()
    {
        $form = $this->getEmptyDummyForm();
        $field = $form->createField('Input', 'test');
        $form->add($field);
        $form->last()->remove($field);
        $this->assertFalse($form->last()->has('test'));
    }

    /**
     * @test
     */
    public function canRemoveBadFieldByNameWithoutErrorAndReturnFalse()
    {
        $form = $this->getEmptyDummyForm();
        $this->assertNull($form->last()->remove('test'));
    }

    /**
     * @test
     */
    public function canRemoveBadFieldByInstanceWithoutErrorAndReturnFalse()
    {
        $form = $this->getEmptyDummyForm();
        $field = Input::create(['type' => 'Input', 'name' => 'badname']);
        $child = $form->last()->remove($field);
        $this->assertNull($child);
    }

    /**
     * @test
     */
    public function canCreateAndAddField()
    {
        $form = $this->getEmptyDummyForm();
        $field = $form->createField('Input', 'input');
        $form->add($field);
        $this->assertIsValidAndWorkingFormObject($form);
        $this->assertTrue($form->last()->has('input'));
    }

    /**
     * @test
     */
    public function canCreateAndAddContainer()
    {
        $form = $this->getEmptyDummyForm();
        $container = $form->createContainer('Section', 'section');
        $form->add($container);
        $this->assertTrue($form->last()->has('section'));
        $this->assertIsValidAndWorkingFormObject($form);
    }

    /**
     * @test
     */
    public function supportsFormComponentsPlacedInPartialTemplates()
    {
        $template = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_USESPARTIAL);
        $form = $this->getDummyFormFromTemplate($template);
        $this->assertIsValidAndWorkingFormObject($form);
    }

    /**
     * @test
     */
    public function canCreateFromDefinition()
    {
        $properties = [
            'name' => 'test',
            'label' => 'Test field'
        ];
        $instance = Form::create($properties);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $instance);
    }

    /**
     * @test
     */
    public function canCreateFromDefinitionWithSheets()
    {
        $properties = [
            'name' => 'test',
            'label' => 'Test field',
            'sheets' => [
                'sheet' => [
                    'fields' => []
                ],
                'anotherSheet' => [
                    'fields' => []
                ],
            ]
        ];
        $instance = Form::create($properties);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $instance);
    }

    /**
     * @test
     */
    public function canDetermineHasChildrenFalse()
    {
        $instance = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $this->assertFalse($instance->hasChildren());
    }

    /**
     * @test
     */
    public function canDetermineHasChildrenTrue()
    {
        $instance = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $instance->createField('Input', 'test');
        $this->assertTrue($instance->hasChildren());
    }

    /**
     * @test
     */
    public function canSetAndGetOptions()
    {
        $instance = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $instance->setOption('test', 'testing');
        $this->assertSame('testing', $instance->getOption('test'));
        $this->assertIsArray($instance->getOptions());
        $this->assertArrayHasKey('test', $instance->getOptions());
        $options = ['foo' => 'bar'];
        $instance->setOptions($options);
        $this->assertSame('bar', $instance->getOption('foo'));
        $this->assertArrayHasKey('foo', $instance->getOptions());
        $this->assertArrayNotHasKey('test', $instance->getOptions());
    }

    /**
     * @test
     */
    public function modifySetsProperty()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->modify(['name' => 'test']);
        $this->assertEquals('test', $form->getName());
    }

    /**
     * @test
     */
    public function modifySetsOptions()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->modify(['options' => ['test' => 'testvalue']]);
        $this->assertEquals('testvalue', $form->getOption('test'));
    }

    /**
     * @test
     */
    public function modifyCreatesSheets()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->modify(['sheets' => ['test' => ['name' => 'test', 'label' => 'Test']]]);
        $sheets = $form->getSheets(true);
        $this->assertArrayHasKey('test', $sheets);
    }

    /**
     * @test
     */
    public function modifyModifiesSheets()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->modify(['sheets' => ['options' => ['label' => 'Test']]]);
        $sheets = $form->getSheets(true);
        $this->assertEquals('Test', reset($sheets)->getLabel());
    }

    public function testSetOptionWithDottedPathAndExistingValue(): void
    {
        $form = new Form();
        $form->setOptions(['foo' => ['bar' => ['baz' => 'value']]]);
        $form->setOption('foo.bar.baz', 'new-value');

        self::assertSame(['foo' => ['bar' => ['baz' => 'new-value']]], $form->getOptions());
    }

    public function testSetOptionWithDottedPathAndNewValue(): void
    {
        $form = new Form();
        $form->setOptions(['foo' => ['bar' => ['baz' => 'value']]]);
        $form->setOption('foo.new', 'new-value');

        self::assertSame(['foo' => ['bar' => ['baz' => 'value'], 'new' => 'new-value']], $form->getOptions());
    }

    public function testModifyCanModifyExistingSheet(): void
    {
        $structure = [
            'sheets' => [
                'foobar' => [
                    'fields' => [
                        'test' => [
                            'label' => 'Foobar',
                        ],
                    ],
                ],
            ],
        ];

        $form = new Form();
        $sheet = $form->createContainer(Form\Container\Sheet::class, 'foobar', 'Foobar');
        $field = $sheet->createField(Input::class, 'test', 'Label');

        $form->modify($structure);

        self::assertSame('Foobar', $field->getLabel());
    }
}
