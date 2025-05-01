<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\PreviewOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\Event\ModifyPageLayoutContentEventListener;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\ServerRequest;

class ModifyPageLayoutContentEventListenerTest extends AbstractTestCase
{
    private PageProvider $pageProvider;
    private ModifyPageLayoutContentEventListener $subject;
    private ModifyPageLayoutContentEvent $event;

    protected function setUp(): void
    {
        if (!class_exists(ModifyPageLayoutContentEvent::class)) {
            $this->markTestSkipped('Skipping test for non-existing event class');
        }
        parent::setUp();

        $moduleTemplateReflection = new \ReflectionClass(ModuleTemplate::class);
        $moduleTemplateReflection->newInstanceWithoutConstructor();

        $this->event = new ModifyPageLayoutContentEvent(
            $this->getMockBuilder(ServerRequest::class)->disableOriginalConstructor()->getMock(),
            $moduleTemplateReflection->newInstanceWithoutConstructor()
        );
        $this->pageProvider = $this->getMockBuilder(PageProvider::class)
            ->onlyMethods(['getForm', 'getPreview'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = $this->getMockBuilder(ModifyPageLayoutContentEventListener::class)
            ->onlyMethods(['getRecord'])
            ->setConstructorArgs([$this->pageProvider])
            ->getMock();
    }

    public function testReturnsEarlyWithoutRecord(): void
    {
        $this->subject->method('getRecord')->willReturn(null);
        $this->pageProvider->expects(self::never())->method('getForm');
        $this->subject->renderPreview($this->event);
        self::assertSame('', $this->event->getHeaderContent());
    }

    public function testReturnsEarlyWithoutForm(): void
    {
        $this->subject->method('getRecord')->willReturn(null);
        $this->pageProvider->method('getForm')->willReturn(null);
        $this->subject->renderPreview($this->event);
        self::assertSame('', $this->event->getHeaderContent());
    }

    public function testReturnsEarlyWithDisabledForm(): void
    {
        $form = Form::create();
        $form->setEnabled(false);
        $this->subject->method('getRecord')->willReturn(['uid' => 123]);
        $this->pageProvider->method('getForm')->willReturn($form);
        $this->subject->renderPreview($this->event);
        self::assertSame('', $this->event->getHeaderContent());
    }

    /**
     * @dataProvider getPreviewTestValues
     */
    public function testGeneratesPreview(string $expected, string $returnedPreview, ?string $mode): void
    {
        $form = Form::create();
        if ($mode) {
            $form->setOption(PreviewOption::PREVIEW, [PreviewOption::MODE => $mode]);
        }
        $this->subject->method('getRecord')->willReturn(['uid' => 123]);
        $this->pageProvider->method('getForm')->willReturn($form);
        $this->pageProvider->method('getPreview')->willReturn(['', $returnedPreview, '']);
        $this->subject->renderPreview($this->event);
        self::assertSame($expected, $this->event->getHeaderContent());
    }

    public function getPreviewTestValues(): array
    {
        return [
            'mode append' => ['preview', 'preview', PreviewOption::MODE_APPEND],
            'mode prepend' => ['preview', 'preview', PreviewOption::MODE_PREPEND],
            'mode replace' => ['preview', 'preview', PreviewOption::MODE_REPLACE],
            'mode none' => ['', 'preview', PreviewOption::MODE_NONE],
            'mode not set' => ['preview', 'preview', null],
            'no preview returned' => ['', '', null],
        ];
    }
}
