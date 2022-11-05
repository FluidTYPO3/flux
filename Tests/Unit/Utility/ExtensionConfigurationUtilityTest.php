<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionConfigurationUtilityTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux'] = ['foo' => 'bar'];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux'], $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']);
    }

    public function testInitializeAndGetOptionsWithArray(): void
    {
        if (class_exists(ExtensionConfiguration::class)) {
            $extensionConfiguration = $this->getMockBuilder(ExtensionConfiguration::class)
                ->setMethods(['get'])
                ->disableOriginalConstructor()
                ->getMock();
            $extensionConfiguration->method('get')->willReturn(['foo' => 'bar']);
            GeneralUtility::addInstance(ExtensionConfiguration::class, $extensionConfiguration);
        }
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = ['foo' => 'bar'];
        ExtensionConfigurationUtility::initialize('');
        self::assertSame(
            ['foo' => 'bar', 'setup' => ['foo' => 'bar'], 'hooks' => []],
            ExtensionConfigurationUtility::getOptions()
        );
        self::assertSame('bar', ExtensionConfigurationUtility::getOption('foo'));
    }
}
