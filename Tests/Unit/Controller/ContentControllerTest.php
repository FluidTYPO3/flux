<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * ContentControllerTest
 */
class ContentControllerTest extends AbstractFluxControllerTestCase
{
    /**
     * @test
     */
    public function canRegisterCustomControllerForContent()
    {
        $this->performDummyRegistration();
    }

    /**
     * @return AbstractFluxController
     */
    protected function createAndTestDummyControllerInstance()
    {
        $this->performDummyRegistration();
        $controllerClassName = 'FluidTYPO3\\Flux\\Controller\\ContentController';
        /** @var AbstractFluxController $instance */
        $instance = new $controllerClassName();
        ObjectAccess::setProperty($instance, 'extensionName', 'Flux', true);
        return $instance;
    }
}
