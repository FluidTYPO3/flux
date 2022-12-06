<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\SubPageProvider;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Fluid\View\TemplatePaths;

class SubPageProviderTest extends AbstractTestCase
{
    protected PageService $pageService;

    protected function setUp(): void
    {
        $this->pageService = $this->getMockBuilder(PageService::class)
            ->setMethods(['getPageTemplateConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->singletonInstances[PageService::class] = $this->pageService;
        $this->singletonInstances[FluxService::class] = $this->getMockBuilder(FluxService::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    /**
     * @dataProvider getControllerActionFromRecordTestValues
     * @param array $record
     * @param string $fieldName
     * @param string $expected
     */
    public function testGetControllerActionFromRecord(array $record, $fieldName, $expected)
    {
        $instance = $this->getMockBuilder(SubPageProvider::class)
            ->setMethods(['dummy'])
            ->getMock();

        $this->pageService->method('getPageTemplateConfiguration')->willReturn($record);

        // make sure PageProvider is now using the right field name
        $instance->trigger($record, null, $fieldName);
        $result = $instance->getControllerActionFromRecord($record);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getControllerActionFromRecordTestValues()
    {
        return array(
            array(array('uid' => 123, 'tx_fed_page_controller_action_sub' => ''), 'tx_fed_page_flexform_sub', 'default'),
            array(array('uid' => 123, 'tx_fed_page_controller_action_sub' => 'flux->action'), 'tx_fed_page_flexform_sub', 'action'),
        );
    }

    public function testGetTemplatePathAndFilename()
    {
        $expected = 'Tests/Fixtures/Templates/Page/Dummy.html';
        $dataFieldName = 'tx_fed_page_flexform_sub';
        $fieldName = 'tx_fed_page_controller_action_sub';
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->setMethods(['resolveTemplateFileForControllerAndActionAndFormat'])
            ->disableOriginalConstructor()
            ->getMock();
        $templatePaths->method('resolveTemplateFileForControllerAndActionAndFormat')->willReturn($expected);
        $instance = $this->getMockBuilder(SubPageProvider::class)
            ->setMethods(['createTemplatePaths'])
            ->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);

        $record = array(
            'uid' => 123,
            $fieldName => 'Flux->dummy',
        );
        $instance->trigger($record, null, $dataFieldName);
        $result = $instance->getTemplatePathAndFilename($record);
        $this->assertEquals($expected, $result);
    }
}
