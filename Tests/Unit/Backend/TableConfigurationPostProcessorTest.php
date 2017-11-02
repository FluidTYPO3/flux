<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TableConfigurationPostProcessorTest
 */
class TableConfigurationPostProcessorTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function canLoadProcessorAsUserObject()
    {
        $object = GeneralUtility::makeInstance('FluidTYPO3\\Flux\\Backend\\TableConfigurationPostProcessor');
        $object->processData();
        $this->assertInstanceOf('FluidTYPO3\\Flux\\Backend\\TableConfigurationPostProcessor', $object);
    }

}
