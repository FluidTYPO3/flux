<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\HookSubscribers\PagePreviewRenderer;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\PageLayoutController;

/**
 * Class PageControllerTest
 */
class PagePreviewRendererTest extends AbstractTestCase
{

    /**
     * @param ProviderInterface $provider
     * @param string $expected
     * @test
     * @dataProvider getRenderTestValues
     */
    public function testRender(ProviderInterface $provider, $expected)
    {
        $subject = $this->getMockBuilder(PagePreviewRenderer::class)->setMethods(['getPageProvider', 'getRecord'])->getMock();
        $subject->expects($this->once())->method('getPageProvider')->willReturn($provider);
        $subject->expects($this->once())->method('getRecord')->with(123)->willReturn(['uid' => 123]);
        $pageLayoutController = $this->getMockBuilder(PageLayoutController::class)->getMock();
        $pageLayoutController->id = 123;
        $result = $subject->render([], $pageLayoutController);
        $this->assertSame($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderTestValues()
    {
        $withForm = new Provider();
        $withForm->setForm(Form::create());
        $withDisabledForm = new Provider();
        $withDisabledForm->setForm(Form::create(['enabled' => false]));
        $withPreview = $this->getMockBuilder(Provider::class)->setMethods(['getForm', 'getPreview'])->getMock();
        $withPreview->expects($this->once())->method('getPreview')->willReturn([null, 'preview', true]);
        $withPreview->expects($this->once())->method('getForm')->willReturn(Form::create());

        return [
            [$withForm, ''],
            [$withDisabledForm, ''],
            [$withPreview, 'preview'],
        ];
    }

}
