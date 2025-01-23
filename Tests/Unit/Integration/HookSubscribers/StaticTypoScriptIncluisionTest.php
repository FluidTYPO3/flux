<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use FluidTYPO3\Flux\Integration\HookSubscribers\StaticTypoScriptInclusion;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class StaticTypoScriptIncluisionTest extends AbstractTestCase
{
    public function test(): void
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '>')) {
            $this->markTestSkipped('Skipping test of feature that is only active on TYPO3 v10.4');
        }

        $templateService = $this->getMockBuilder(TemplateService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $applicator = $this->getMockBuilder(SpooledConfigurationApplicator::class)
            ->onlyMethods(['processData'])
            ->disableOriginalConstructor()
            ->getMock();
        $applicator->expects(self::once())->method('processData');

        $subject = new StaticTypoScriptInclusion($applicator);
        $subject->includeStaticTypoScriptHook([], $templateService);
    }
}
