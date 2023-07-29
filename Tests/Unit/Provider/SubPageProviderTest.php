<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\View\TemplatePaths;

class SubPageProviderTest extends PageProviderTest
{
    public function getControllerActionFromRecordTestValues(): array
    {
        return [
            [['uid' => 123, 'tx_fed_page_controller_action_sub' => ''], 'tx_fed_page_flexform_sub', 'default'],
            [['uid' => 123, 'tx_fed_page_controller_action_sub' => 'flux->action'], 'tx_fed_page_flexform_sub', 'action'],
        ];
    }

    public function testGetTemplatePathAndFilename(): void
    {
        $expected = 'Tests/Fixtures/Templates/Page/Dummy.html';
        $dataFieldName = 'tx_fed_page_flexform_sub';
        $fieldName = 'tx_fed_page_controller_action_sub';
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->onlyMethods(['resolveTemplateFileForControllerAndActionAndFormat'])
            ->disableOriginalConstructor()
            ->getMock();
        $templatePaths->method('resolveTemplateFileForControllerAndActionAndFormat')->willReturn($expected);
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['createTemplatePaths'])
            ->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);

        $record = [
            'uid' => 123,
            $fieldName => 'Flux->dummy',
        ];
        $instance->trigger($record, null, $dataFieldName);
        $result = $instance->getTemplatePathAndFilename($record);
        $this->assertEquals($expected, $result);
    }
}
