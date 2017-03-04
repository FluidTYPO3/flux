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
        CompatibilityRegistry::register('foo', $versionedValues, false);
        $this->assertEquals($expected, CompatibilityRegistry::get('foo', $version, $default));
    }

    /**
     * @return array
     */
    public function getRegisterAndRetrieveTestValues()
    {
        return array(
            'compares version correctly above highest version' => array(
                array(
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ),
                '4.0.0',
                null,
                'baz'
            ),
            'compares version correctly (returns NULL) below lowest version' => array(
                array(
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ),
                '0.5.0',
                null,
                null
            ),
            'compares version correctly between versions' => array(
                array(
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ),
                '1.5.0',
                null,
                'bar'
            ),
            'uses default if no versionable variable applies' => array(
                array(
                    '1.0.0' => 'bar',
                    '2.0.0' => 'baz'
                ),
                '0.5.0',
                'default',
                'default'
            ),
        );
    }

    /**
     * @param array $versionedValues
     * @param string $version
     * @param mixed $flag
     * @param mixed $expected
     * @test
     * @dataProvider getRegisterAndRetrieveFeatureFlagTestValues
     */
    public function testRegisterAndRetieveFeatureFlag(array $versionedValues, $version, $flag, $expected)
    {
        CompatibilityRegistry::registerFeatureFlags($scope, $versionedValues, false);
        $this->assertEquals($expected, CompatibilityRegistry::hasFeatureFlag($scope, $flag, $version));
    }

    /**
     * @return array
     */
    public function getRegisterAndRetrieveFeatureFlagTestValues()
    {
        return array(
            'compares version correctly above highest version' => array(
                array(
                    '1.0.0' => array('foo', 'bar'),
                    '2.0.0' => array('baz')
                ),
                '4.0.0',
                'baz',
                true
            ),
            'compares version correctly below lowest version' => array(
                array(
                    '1.0.0' => array('foo', 'bar'),
                    '2.0.0' => array('baz')
                ),
                '0.5.0',
                'baz',
                false
            ),
            'compares version correctly (positive) between versions' => array(
                array(
                    '1.0.0' => array('foo', 'bar'),
                    '2.0.0' => array('baz')
                ),
                '1.5.0',
                'foo',
                true
            ),
            'compares version correctly (negative) between versions' => array(
                array(
                    '1.0.0' => array('foo', 'bar'),
                    '2.0.0' => array('baz')
                ),
                '1.5.0',
                'baz',
                false
            ),
        );
    }
}
