<?php
namespace FluidTYPO3\Flux\Tests\Unit;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Field\Custom;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractTestCase extends TestCase
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

    protected array $singletonInstances = [];
    private array $singletonInstancesBackup = [];

    protected function setUp(): void
    {
        if (!defined('LF')) {
            define('LF', PHP_EOL);
        }
        if (!defined('TYPO3_REQUESTTYPE')) {
            define('TYPO3_REQUESTTYPE', 1);
        }
        if (!defined('TYPO3_REQUESTTYPE_FE')) {
            define('TYPO3_REQUESTTYPE_FE', 1);
        }
        if (!defined('TYPO3_version')) {
            define('TYPO3_version', '9.5.0');
        }

        $GLOBALS['EXEC_TIME'] = time();
        $GLOBALS['LANG'] = (object) ['csConvObj' => new CharsetConverter()];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['preProcessors'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['interceptors'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['fluid_template'] = [
            'frontend' => VariableFrontend::class,
            'backend' => TransientMemoryBackend::class,
        ];

        $this->singletonInstancesBackup = GeneralUtility::getSingletonInstances();
        foreach ($this->singletonInstances as $className => $singletonInstance) {
            GeneralUtility::setSingletonInstance($className, $singletonInstance);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        GeneralUtility::purgeInstances();
        GeneralUtility::resetSingletonInstances($this->singletonInstancesBackup);
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
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     * @return void
     */
    protected function setInaccessiblePropertyValue(object $object, string $propertyName, $value): void
    {
        $reflectionProperty = new \ReflectionProperty($object, $propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    protected function getInaccessiblePropertyValue(object $object, string $propertyName)
    {
        $reflectionProperty = new \ReflectionProperty($object, $propertyName);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
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
        $expectedValue = $expectedValue ?? $value;
        if (true === $expectsChaining) {
            $this->assertSame($instance, $chained);
        } else {
            $this->assertNull($chained);
        }
        $this->assertEquals($expectedValue, $instance->$getter());
    }

    /**
     * Asserts that a variable is of type array.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert array $actual
     */
    public static function assertIsArray($actual, string $message = ''): void
    {
        if (class_exists(IsType::class)) {
            $constraint = new IsType(IsType::TYPE_ARRAY);
        } else {
            $constraint = new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
        }
        static::assertThat(
            $actual,
            $constraint,
            $message
        );
    }

    /**
     * @param mixed $value
     * @return void
     */
    protected function assertIsInteger($value)
    {
        $isIntegerConstraint = new IsType(IsType::TYPE_INT);
        $this->assertThat($value, $isIntegerConstraint);
    }

    /**
     * @param mixed $value
     * @return void
     */
    protected function assertIsBoolean($value)
    {
        $isBooleanConstraint = new IsType(IsType::TYPE_BOOL);
        $this->assertThat($value, $isBooleanConstraint);
    }

    /**
     * @param mixed $value
     */
    protected function assertIsValidAndWorkingFormObject($value)
    {
        $this->assertInstanceOf(Form::class, $value);
        $this->assertInstanceOf(Form\FormInterface::class, $value);
        $this->assertInstanceOf(Form\ContainerInterface::class, $value);
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
        $this->assertInstanceOf(Form\Container\Grid::class, $value);
        $this->assertInstanceOf(Form\ContainerInterface::class, $value);
        /** @var Form $value */
        $structure = $value->build();
        $this->assertIsArray($structure);
    }

    protected function getShorthandFixtureTemplatePathAndFilename(): string
    {
        return self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
    }

    /**
     * @param string $shorthandTemplatePath
     * @return string
     */
    protected function getAbsoluteFixtureTemplatePathAndFilename($shorthandTemplatePath)
    {
        return realpath(str_replace('EXT:flux/', './', $shorthandTemplatePath));
    }

    protected function createInstanceClassName(): string
    {
        return str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $instanceClassName = $this->createInstanceClassName();
        return $this->getMockBuilder($instanceClassName)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
