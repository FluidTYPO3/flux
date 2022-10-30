<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Package\PackageManager;

/**
 * WorkspacesAwareRecordServiceTest
 */
class WorkspacesAwareRecordServiceTest extends RecordServiceTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->setMethods(['isPackageActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $packageManager->method('isPackageActive')->willReturn(true);

        AccessibleExtensionManagementUtility::setPackageManager($packageManager);

        $GLOBALS['BE_USER'] = $this->getMockBuilder(BackendUserAuthentication::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        AccessibleExtensionManagementUtility::setPackageManager(null);

        unset($GLOBALS['BE_USER']);
    }

    /**
     * @test
     */
    public function overlayRecordsCallsExpectedMethodSequence()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(array('hasWorkspacesSupport', 'overlayRecord'))
            ->getMock();
        $mock->expects($this->once())->method('hasWorkspacesSupport')->will($this->returnValue(true));
        $mock->expects($this->exactly(2))->method('overlayRecord')->will($this->returnValue(array('foo')));
        $records = array(array(), array());
        $expected = array(array('foo'), array('foo'));
        $result = $this->callInaccessibleMethod($mock, 'overlayRecords', 'table', $records);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getWorkspaceVersionOfRecordOrRecordItselfReturnsSelf()
    {
        $instance = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['overlayRecordInternal'])
            ->getMock();
        $instance->method('overlayRecordInternal')->willReturn(false);
        $result = $this->callInaccessibleMethod(
            $instance,
            'getWorkspaceVersionOfRecordOrRecordItself',
            'void',
            array('uid' => 1)
        );
        $this->assertEquals(array('uid' => 1), $result);
    }
}
