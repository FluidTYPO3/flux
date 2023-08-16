<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Overrides\LegacyChimeraConfigurationManager;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

class LegacyChimeraConfigurationManagerTest extends AbstractTestCase
{
    public function test(): void
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '>=')) {
            self::markTestSkipped('Skipping legacy chimera configuration manager test');
        }
        $objectManager = $this->getMockBuilder(ObjectManager::class)
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects(self::exactly(3))->method('get')->willReturnMap(
            [
                [
                    FrontendConfigurationManager::class,
                    $this->getMockBuilder(FrontendConfigurationManager::class)->disableOriginalConstructor()->getMock(),
                ],
                [
                    BackendConfigurationManager::class,
                    $this->getMockBuilder(BackendConfigurationManager::class)->disableOriginalConstructor()->getMock(),
                ],
            ]
        );

        $environmentService = $this->getMockBuilder(EnvironmentService::class)->disableOriginalConstructor()->getMock();

        $subject = $this->getMockBuilder(LegacyChimeraConfigurationManager::class)
            ->onlyMethods(['refreshRequestIfNecessary'])
            ->setConstructorArgs([$objectManager, $environmentService])
            ->getMock();
    }
}
