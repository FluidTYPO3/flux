<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TypoScriptTemplateTest
 */
class TypoScriptTemplateTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function canPreProcessIncludeStaticTypoScriptResources()
    {
        Core::addStaticTypoScript(self::FIXTURE_TYPOSCRIPT_DIR);
        $function = 'FluidTYPO3\Flux\Backend\TypoScriptTemplate->preprocessIncludeStaticTypoScriptSources';
        $template = $this->objectManager->get('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
        $parameters = array(
            'row' => Records::$sysTemplateRoot
        );
        GeneralUtility::callUserFunction($function, $parameters, $template);
        $this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $parameters['row']['include_static_file']);
    }

    /**
     * @test
     */
    public function leavesRecordsWhichAreNotRootsUntouched()
    {
        Core::addStaticTypoScript(self::FIXTURE_TYPOSCRIPT_DIR);
        $function = 'FluidTYPO3\Flux\Backend\TypoScriptTemplate->preprocessIncludeStaticTypoScriptSources';
        $template = $this->objectManager->get('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
        $parameters = array(
            'row' => Records::$sysTemplateRoot
        );
        $parameters['row']['root'] = 0;
        GeneralUtility::callUserFunction($function, $parameters, $template);
        $this->assertNotContains(self::FIXTURE_TYPOSCRIPT_DIR, $parameters['row']['include_static_file']);
        $this->assertSame(Records::$sysTemplateRoot['include_static_file'], $parameters['row']['include_static_file']);
    }
}
