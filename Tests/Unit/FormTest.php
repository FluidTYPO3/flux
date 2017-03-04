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
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;

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
        /** @var Form $form */
        $form = $this->objectManager->get('FluidTYPO3\Flux\Form');
        return $form;
    }

    /**
     * @param string $template
     * @return Form
     */
    protected function getDummyFormFromTemplate($template = self::FIXTURE_TEMPLATE_BASICGRID)
    {
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename($template);
        $service = $this->createFluxServiceInstance();
        $viewContext = new ViewContext($templatePathAndFilename, 'Flux');
        $viewContext->setSectionName('Configuration');
        $form = $service->getFormFromTemplateFile($viewContext);
        return $form;
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
    public function canUseGroupProperty()
    {
        $form = $this->getDummyFormFromTemplate();
        $group = 'dummyGroup';
        $form->setGroup($group);
        $this->assertSame($group, $form->getGroup());
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
        $object = $form->createContainer('Object', 'object');
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
        $fields = array(
            $form->createField('Input', 'test1'),
            $form->createField('Input', 'test2'),
        );
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
        $this->assertFalse($form->last()->remove('test'));
    }

    /**
     * @test
     */
    public function canRemoveBadFieldByInstanceWithoutErrorAndReturnFalse()
    {
        $form = $this->getEmptyDummyForm();
        $field = Input::create(array('type' => 'Input', 'name' => 'badname'));
        $child = $form->last()->remove($field);
        $this->assertFalse($child);
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
    public function canCreateAndAddWizard()
    {
        $form = $this->getEmptyDummyForm();
        $field = $form->createField('Input', 'input');
        $wizard = $form->createWizard('Add', 'add');
        $field->add($wizard);
        $form->add($field);
        $this->assertIsValidAndWorkingFormObject($form);
    }

    /**
     * @test
     */
    public function supportsFormComponentsPlacedInPartialTemplates()
    {
        $template = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_USESPARTIAL);
        $service = $this->createFluxServiceInstance();
        $paths = array(
            'templateRootPaths' => array('EXT:flux/Tests/Fixtures/Templates/'),
            'partialRootPaths' => array('EXT:flux/Tests/Fixtures/Partials/'),
            'layoutRootPaths' => array('EXT:flux/Tests/Fixtures/Layouts/')
        );
        $viewContext = new ViewContext($template);
        $viewContext->setTemplatePaths(new TemplatePaths($paths));
        $viewContext->setSectionName('Configuration');
        $form = $service->getFormFromTemplateFile($viewContext);
        $this->assertIsValidAndWorkingFormObject($form);
    }

    /**
     * @test
     */
    public function canCreateFromDefinition()
    {
        $properties = array(
            'name' => 'test',
            'label' => 'Test field'
        );
        $instance = Form::create($properties);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $instance);
    }

    /**
     * @test
     */
    public function canCreateFromDefinitionWithSheets()
    {
        $properties = array(
            'name' => 'test',
            'label' => 'Test field',
            'sheets' => array(
                'sheet' => array(
                    'fields' => array()
                ),
                'anotherSheet' => array(
                    'fields' => array()
                ),
            )
        );
        $instance = Form::create($properties);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $instance);
    }

    /**
     * @test
     */
    public function canDetermineHasChildrenFalse()
    {
        $instance = Form::create();
        $this->assertFalse($instance->hasChildren());
    }

    /**
     * @test
     */
    public function canDetermineHasChildrenTrue()
    {
        $instance = Form::create();
        $instance->createField('Input', 'test');
        $this->assertTrue($instance->hasChildren());
    }

    /**
     * @test
     */
    public function canSetAndGetOptions()
    {
        $instance = Form::create();
        $instance->setOption('test', 'testing');
        $this->assertSame('testing', $instance->getOption('test'));
        $this->assertIsArray($instance->getOptions());
        $this->assertArrayHasKey('test', $instance->getOptions());
        $options = array('foo' => 'bar');
        $instance->setOptions($options);
        $this->assertSame('bar', $instance->getOption('foo'));
        $this->assertArrayHasKey('foo', $instance->getOptions());
        $this->assertArrayNotHasKey('test', $instance->getOptions());
    }

    /**
     * @test
     */
    public function canSetAndGetOutlet()
    {
        /** @var StandardOutlet $outlet */
        $outlet = $this->getMockBuilder('FluidTYPO3\Flux\Outlet\StandardOutlet')->getMock();
        $form = Form::create();
        $form->setOutlet($outlet);
        $this->assertSame($outlet, $form->getOutlet());
    }

    /**
     * @test
     */
    public function dispatchesDebugMessageOnProblematicId()
    {
        $service = $this->getMockBuilder('FluidTYPO3\Flux\Service\FluxService')->setMethods(array('message'))->getMock();
        $service->expects($this->once())->method('message');
        $instance = $this->getMockBuilder('FluidTYPO3\\Flux\\Form')->setMethods(array('getConfigurationService'))->getMock();
        $instance->expects($this->once())->method('getConfigurationService')->willReturn($service);
        $instance->setId('I-am-not-valid');
    }

    /**
     * @test
     */
    public function modifySetsProperty()
    {
        $form = Form::create();
        $form->modify(array('name' => 'test'));
        $this->assertEquals('test', $form->getName());
    }

    /**
     * @test
     */
    public function modifySetsOptions()
    {
        $form = Form::create();
        $form->modify(array('options' => array('test' => 'testvalue')));
        $this->assertEquals('testvalue', $form->getOption('test'));
    }

    /**
     * @test
     */
    public function modifyCreatesSheets()
    {
        $form = Form::create();
        $form->modify(array('sheets' => array('test' => array('name' => 'test', 'label' => 'Test'))));
        $sheets = $form->getSheets(true);
        $this->assertArrayHasKey('test', $sheets);
    }

    /**
     * @test
     */
    public function modifyModifiesSheets()
    {
        $form = Form::create();
        $form->modify(array('sheets' => array('options' => array('label' => 'Test'))));
        $sheets = $form->getSheets(true);
        $this->assertEquals('Test', reset($sheets)->getLabel());
    }
}
