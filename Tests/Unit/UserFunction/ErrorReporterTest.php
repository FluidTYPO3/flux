<?php
namespace FluidTYPO3\Flux\Tests\Unit\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ErrorReporterTest
 */
class ErrorReporterTest extends AbstractUserFunctionTest
{

    const FAKE_MESSAGE = 'This is a demo Exception';
    const FAKE_CODE = 1374506190;

    /**
     * @var array
     */
    protected $parameters = array(
        'fieldConf' => array(
            'config' => array(
                'arguments' => array(
                )
            )
        )
    );

    /**
     * @return array
     */
    protected function getParameters()
    {
        $parameters = $this->parameters;
        $parameters['fieldConf']['config']['arguments'] = array(
            'Ignored text ' . self::FAKE_MESSAGE . ' (' . self::FAKE_CODE . ') ignored text'
        );
        return $parameters;
    }

    /**
     * @test
     */
    public function supportsExceptionAsParameter()
    {
        $userFunctionReference = $this->getClassName() . '->' . $this->methodName;
        $parameters = $this->getParameters();
        $parameters['fieldConf']['config']['arguments'] = array(new \Exception(self::FAKE_MESSAGE, self::FAKE_CODE));
        $output = GeneralUtility::callUserFunction($userFunctionReference, $parameters, $this->getCallerInstance());
        $this->assertOutputContainsExpectedMessageAndCode($output);
    }

    /**
     * @test
     */
    public function renderedErrorReportContainsExceptionMessageAndCode()
    {
        $output = $this->canCallMethodAndReceiveOutput();
        $this->assertOutputContainsExpectedMessageAndCode($output);
    }

    /**
     * @param string $output
     */
    protected function assertOutputContainsExpectedMessageAndCode($output)
    {
        $this->assertContains(self::FAKE_MESSAGE, $output);
        $this->assertContains(strval(self::FAKE_CODE), $output);
    }
}
