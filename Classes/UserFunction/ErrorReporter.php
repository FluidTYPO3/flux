<?php
namespace FluidTYPO3\Flux\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Renders an exception error message in a nice way
 * @deprecated To be removed in next major release
 */
class ErrorReporter
{

    /**
     * @param array $parameters
     * @param object $pObj Not used
     * @return string
     */
    public function renderField(&$parameters, &$pObj)
    {
        unset($pObj);
        $exception = reset($parameters['fieldConf']['config']['arguments']);
        if ($exception instanceof \Exception) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $type = get_class($exception);
            return 'An ' . $type . ' was encountered while rendering the FlexForm.<br /><br />
				The error code is ' . $code . ' and the message states: ' . $message;
        } else {
            return 'An error was encountered while rendering the FlexForm.<br /><br />
					The error message states: ' . $exception;
        }
    }
}
