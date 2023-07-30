<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CompatibilityRegistryTest
 */
class CompatibilityRegistryTest extends AbstractTestCase
{
    /**
     * @param array $versionedValues
     * @param string $version
     * @param mixed $default
     * @param mixed $expected
     * @test
     * @dataProvider getRegisterAndRetrieveTestValues
     */
    public function testRegisterAndRetieve(array $versionedValues, $version, $default, $expected)
    {
        $versionClass = $this->getMockBuilder(Typo3Version::class)
            ->setMethods(['getVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $versionClass->method('getVersion')->willReturn($version);
        GeneralUtility::addInstance(Typo3Version::class, $versionClass);
        CompatibilityRegistry::register('foo', $versionedValues);
        $this->assertEquals($expected, CompatibilityRegistry::get('foo', $version, $default));
    }

    /**
     * @return array
     */
    public function getRegisterAndRetrieveTestValues()
    {
        return [
            'compares version correctly above highest version' => [
                [
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ],
                '4.0.0',
                null,
                'baz'
            ],
            'compares version correctly (returns NULL) below lowest version' => [
                [
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ],
                '0.5.0',
                null,
                null
            ],
            'compares version correctly between versions' => [
                [
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ],
                '1.5.0',
                null,
                'bar'
            ],
            'uses default if no versionable variable applies' => [
                [
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ],
                '0.5.0',
                'default',
                'default'
            ],
        ];
    }

    /**
     * @param array $versionedValues
     * @param string $version
     * @param mixed $flag
     * @param mixed $expected
     * @test
     * @dataProvider getRegisterAndRetrieveFeatureFlagTestValues
     */
    public function testRegisterAndRetrieveFeatureFlag(array $versionedValues, $version, $flag, $expected)
    {
        $versionClass = $this->getMockBuilder(Typo3Version::class)
            ->setMethods(['getVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $versionClass->method('getVersion')->willReturn($version);
        GeneralUtility::addInstance(Typo3Version::class, $versionClass);
        CompatibilityRegistry::registerFeatureFlags('xyz', $versionedValues);
        $this->assertEquals($expected, CompatibilityRegistry::hasFeatureFlag('xyz', $flag, $version));
    }

    /**
     * @return array
     */
    public function getRegisterAndRetrieveFeatureFlagTestValues()
    {
        return [
            'compares version correctly above highest version' => [
                [
                    '1.0.0' => ['foo', 'bar'],
                    '2.0.0' => ['baz']
                ],
                '4.0.0',
                'baz',
                true
            ],
            'compares version correctly below lowest version' => [
                [
                    '1.0.0' => ['foo', 'bar'],
                    '2.0.0' => ['baz']
                ],
                '0.5.0',
                'baz',
                false
            ],
            'compares version correctly (positive) between versions' => [
                [
                    '1.0.0' => ['foo', 'bar'],
                    '2.0.0' => ['baz']
                ],
                '1.5.0',
                'foo',
                true
            ],
            'compares version correctly (negative) between versions' => [
                [
                    '1.0.0' => ['foo', 'bar'],
                    '2.0.0' => ['baz']
                ],
                '1.5.0',
                'baz',
                false
            ],
        ];
    }
}
