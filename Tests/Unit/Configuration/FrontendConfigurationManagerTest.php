<?php
namespace FluidTYPO3\Flux\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * FrontendConfigurationManagerTest
 */
class FrontendConfigurationManagerTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testGetConfigurationReturnsArrayIfNoTypoScriptExists()
    {
        $manager = new FrontendConfigurationManager();
        $GLOBALS['TSFE'] = (object) ['tmpl' => (object) ['setup' => null]];
        $result = $manager->getTypoScriptSetup();
        $this->assertSame([], $result);
    }
}
