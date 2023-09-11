<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\ContentController;
use Psr\Http\Message\ServerRequestInterface;

class ContentControllerTest extends AbstractFluxControllerTestCase
{
    public function testCanRegisterCustomControllerForContent(): void
    {
        $this->performDummyRegistration();
    }

    protected function createAndTestDummyControllerInstance(): ContentController
    {
        $this->performDummyRegistration();
        $controllerClassName = ContentController::class;
        /** @var ContentController $instance */
        $instance = $this->getMockBuilder($controllerClassName)
            ->onlyMethods(['getContentObject', 'getServerRequest'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $this->setInaccessiblePropertyValue($instance, 'extensionName', 'Flux');
        return $instance;
    }

    public function testCanGetRecord(): void
    {
        parent::testCanGetRecord();
    }
}
