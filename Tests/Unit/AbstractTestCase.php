<?php
namespace FluidTYPO3\Flux\Tests\Unit;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Development\Bootstrap;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Field\Custom;
use FluidTYPO3\Flux\Service\FluxService;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\View\GenericViewResolver;
use TYPO3\CMS\Extbase\Mvc\View\ViewResolverInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * AbstractTestCase
 */
abstract class AbstractTestCase extends \FluidTYPO3\Development\AbstractTestCase
{

    const FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL = 'EXT:flux/Tests/Fixtures/Templates/Content/AbsolutelyMinimal.html';
    const FIXTURE_TEMPLATE_WITHOUTFORM = 'EXT:flux/Tests/Fixtures/Templates/Content/WithoutForm.html';
    const FIXTURE_TEMPLATE_SHEETS = 'EXT:flux/Tests/Fixtures/Templates/Content/Sheets.html';
    const FIXTURE_TEMPLATE_USESPARTIAL = 'EXT:flux/Tests/Fixtures/Templates/Content/UsesPartial.html';
    const FIXTURE_TEMPLATE_CUSTOM_SECTION = 'EXT:flux/Tests/Fixtures/Templates/Content/CustomSection.html';
    const FIXTURE_TEMPLATE_PREVIEW_EMPTY = 'EXT:flux/Tests/Fixtures/Templates/Content/EmptyPreview.html';
    const FIXTURE_TEMPLATE_PREVIEW = 'EXT:flux/Tests/Fixtures/Templates/Content/Preview.html';
    const FIXTURE_TEMPLATE_BASICGRID = 'EXT:flux/Tests/Fixtures/Templates/Content/BasicGrid.html';
    const FIXTURE_TEMPLATE_DUALGRID = 'EXT:flux/Tests/Fixtures/Templates/Content/DualGrid.html';
    const FIXTURE_TEMPLATE_COLLIDINGGRID = 'EXT:flux/Tests/Fixtures/Templates/Content/CollidingGrid.html';
    const FIXTURE_TYPOSCRIPT_DIR = 'EXT:flux/Tests/Fixtures/Data/TypoScript';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectContainer = Bootstrap::getInstance()->getObjectContainer();
        $objectManager = GeneralUtility::makeInstance(
            ObjectManager::class,
            Bootstrap::getInstance()->getContainer(),
            $objectContainer
        );
        $objectContainer->registerImplementation(
            ViewResolverInterface::class,
            GenericViewResolver::class
        );
        $objectContainer->registerImplementation(
            ContainerInterface::class,
            get_class(Bootstrap::getInstance()->getContainer())
        );
        $objectContainer->registerImplementation(
            EventDispatcherInterface::class,
            EventDispatcher::class
        );
        $objectContainer->registerImplementation(
            ListenerProviderInterface::class,
            ListenerProvider::class
        );
        GeneralUtility::setSingletonInstance(ObjectManagerInterface::class, $objectManager);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManager);
        $this->objectManager = clone $objectManager;
        $GLOBALS['LANG'] = $this->getMockBuilder(LanguageService::class)->disableOriginalConstructor()->getMock();
        $GLOBALS['BE_USER'] = new BackendUserAuthentication();
        $GLOBALS['BE_USER']->setLogger($this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass());
    }

    /**
     * Helper function to call protected or private methods
     *
     * @param object $object The object to be invoked
     * @param string $name the name of the method to call
     * @param mixed $arguments
     * @return mixed
     */
    protected function callInaccessibleMethod($object, $name, ...$arguments)
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethod = $reflectionObject->getMethod($name);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $arguments);
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     * @param mixed $expectedValue
     * @param mixed $expectsChaining
     * @return void
     */
    protected function assertGetterAndSetterWorks($propertyName, $value, $expectedValue = null, $expectsChaining = false)
    {
        $instance = $this->createInstance();
        $setter = 'set' . ucfirst($propertyName);
        $getter = 'get' . ucfirst($propertyName);
        $chained = $instance->$setter($value);
        if (true === $expectsChaining) {
            $this->assertSame($instance, $chained);
        } else {
            $this->assertNull($chained);
        }
        $this->assertEquals($expectedValue, $instance->$getter());
    }

    /**
     * @param mixed $value
     */
    protected function assertIsValidAndWorkingFormObject($value)
    {
        $this->assertInstanceOf('FluidTYPO3\Flux\Form', $value);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $value);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\ContainerInterface', $value);
        /** @var Form $value */
        $structure = $value->build();
        $this->assertIsArray($structure);
        // scan for and attempt building of closures in structure
        foreach ($value->getFields() as $field) {
            if (true === $field instanceof Custom) {
                $closure = $field->getClosure();
                $output = $closure($field->getArguments());
                $this->assertNotEmpty($output);
            }
        }
    }

    /**
     * @param mixed $value
     */
    protected function assertIsValidAndWorkingGridObject($value)
    {
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $value);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\ContainerInterface', $value);
        /** @var Form $value */
        $structure = $value->build();
        $this->assertIsArray($structure);
    }

    /**
     * @return string
     */
    protected function getShorthandFixtureTemplatePathAndFilename()
    {
        return self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
    }

    /**
     * @param string $shorthandTemplatePath
     * @return string
     */
    protected function getAbsoluteFixtureTemplatePathAndFilename($shorthandTemplatePath)
    {
        return GeneralUtility::getFileAbsFileName($shorthandTemplatePath);
    }

    /**
     * @param array $methods
     * @return FluxService
     */
    protected function createFluxServiceInstance($methods = array('dummy'))
    {
        /** @var FluxService $fluxService */
        $fluxService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods($methods)->disableOriginalConstructor()->getMock();
        $fluxService->injectObjectManager($this->objectManager);
        $configurationManager = $this->getMockBuilder(ConfigurationManager::class)->disableOriginalConstructor()->getMock();
        $fluxService->injectConfigurationManager($configurationManager);
        return $fluxService;
    }

    /**
     * @return object
     */
    protected function createInstanceClassName()
    {
        return str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $className = $this->createInstanceClassName();
        return $this->getMockBuilder($className)->addMethods(['dummy'])->disableOriginalConstructor()->getMock();
    }

}
