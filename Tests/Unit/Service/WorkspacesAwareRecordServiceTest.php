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

        unset($GLOBALS['BE_USER']);
    }

    public function testGetSinglePerformsOverlay(): void
    {
        $table = 'test';
        $fields = 'a,b';
        $uid = 123;
        $mock = $this->getMockServiceInstance(['hasWorkspacesSupport', 'overlayRecordInternal']);
        $mock->expects(self::once())->method('hasWorkspacesSupport')->willReturn(true);
        $mock->expects(self::once())
            ->method('overlayRecordInternal')
            ->with($table, ['uid' => $uid])
            ->willReturn(['uid' => 456]);

        $this->createAndRegisterMockForQueryBuilder([['uid' => $uid]]);

        $mock->getSingle($table, $fields, $uid);
    }

    /**
     * @test
     */
    public function overlayRecordsCallsExpectedMethodSequence()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(['hasWorkspacesSupport', 'overlayRecordInternal'])
            ->getMock();
        $mock->expects($this->once())->method('hasWorkspacesSupport')->will($this->returnValue(true));
        $mock->expects($this->exactly(2))
            ->method('overlayRecordInternal')
            ->willReturnOnConsecutiveCalls($this->returnValue(['foo']), false);
        $records = [[], []];
        $expected = [['foo']];
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
            ['uid' => 1]
        );
        $this->assertEquals(['uid' => 1], $result);
    }
}
