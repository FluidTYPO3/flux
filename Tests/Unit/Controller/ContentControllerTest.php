<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Fixtures\Classes\ContentController;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\Controller\AbstractFluxControllerTestCase;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $record['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
        $record['tx_fed_fcefile'] = 'Flux:Default.html';
        $this->performDummyRegistration();
        $controllerClassName = 'FluidTYPO3\\Flux\\Controller\\ContentController';
        /** @var AbstractFluxController $instance */
        $instance = $this->objectManager->get($controllerClassName);
        ObjectAccess::setProperty($instance, 'extensionName', 'Flux', true);
        return $instance;
    }
}
