<?php
namespace FluidTYPO3\Flux\Tests\Unit\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractUserFunctionTest
 */
abstract class AbstractUserFunctionTest extends AbstractTestCase
{

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $methodName = 'renderField';

    /**
     * @var boolean
     */
    protected $expectsNull = false;

    /**
     * @return array
     */
    protected function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    protected function getClassName()
    {
        $className = substr(get_class($this), 0, -4);
        $className = str_replace('Tests\\Unit\\', '', $className);
        return $className;
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $className = $this->getClassName();
        $instance = $this->objectManager->get($className);
        return $instance;
    }

    /**
     * @return FormEngine
     */
    protected function getCallerInstance()
    {
        return $this->getMockBuilder('TYPO3\\CMS\\Backend\\Form\\FormEngine')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        $instance = $this->createInstance();
        $this->assertInstanceOf($this->getClassName(), $instance);
    }

    /**
     * @test
     */
    public function canCallMethodAndReceiveOutput()
    {
        $reference = $this->getCallerInstance();
        $parameters = $this->getParameters();
        $output = call_user_func_array(array($this->getClassName(), $this->methodName), array(&$parameters, &$reference));
        if (true === $this->expectsNull) {
            $this->assertNull($output);
        } else {
            $this->assertNotEmpty($output);
        }
        return $output;
    }
}
