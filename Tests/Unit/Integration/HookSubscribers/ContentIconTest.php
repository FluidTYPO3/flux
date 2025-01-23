<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\HookSubscribers\ContentIcon;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Proxy\IconFactoryProxy;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Lang\LanguageService;

class ContentIconTest extends AbstractTestCase
{
    private ?ObjectManagerInterface $objectManager;
    private ?ProviderResolver $providerResolver;
    private ?CacheManager $cacheManager;
    private ?FrontendInterface $cache;
    private ?IconFactoryProxy $iconFactory;

    protected function setUp(): void
    {
        $this->cache = $this->createStub(FrontendInterface::class);
        $this->providerResolver = $this->createStub(ProviderResolver::class);
        $this->cacheManager = $this->createStub(CacheManager::class);
        $this->cacheManager->method('getCache')->willReturn($this->cache);
        $this->iconFactory = $this->createStub(IconFactoryProxy::class);

        parent::setUp();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->providerResolver,
            $this->iconFactory,
            $this->cacheManager,
        ];
    }

    public function testCreatesInstancesInConstructor(): void
    {
        $subject = new ContentIcon(...$this->getConstructorArguments());
        self::assertInstanceOf(
            ProviderResolver::class,
            $this->getInaccessiblePropertyValue($subject, 'providerResolver')
        );
        self::assertInstanceOf(FrontendInterface::class, $this->getInaccessiblePropertyValue($subject, 'cache'));
    }

    public function testAddSubIconUsesCache(): void
    {
        $this->cache->expects($this->once())->method('get')->willReturn('icon');
        $instance = $this->getMockBuilder(ContentIcon::class)
            ->onlyMethods(['drawGridToggle'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->method('drawGridToggle')->willReturn('foobar');
        $this->setInaccessiblePropertyValue($instance, 'cache', $this->cache);

        $callerClassName = version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '<=')
            ? PageLayoutView::class
            : GridColumnItem::class;

        $result = $instance->addSubIcon(
            [
                'tt_content', 123,
                ['foo' => 'bar']
            ],
            $this->createStub($callerClassName)
        );
        $this->assertEquals('icon', $result);
    }

    public function testDrawGridToggle(): void
    {
        $icon = $this->createStub(Icon::class);
        $icon->method('render')->willReturn('foobar');

        $this->iconFactory->method('getIcon')->willReturn($icon);

        $subject = $this->getMockBuilder(ContentIcon::class)
            ->onlyMethods(['translate'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $subject->method('translate')->willReturnArgument(0);

        $result = $this->callInaccessibleMethod($subject, 'drawGridToggle', ['uid' => 123]);
        $this->assertStringContainsString(
            'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:toggle_content',
            $result
        );
        $this->assertStringContainsString('foobar', $result);
    }

    /**
     * @dataProvider getAddSubIconTestValues
     */
    public function testAddSubIcon(array $parameters, ?ProviderInterface $provider): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['field']['config']['type'] = 'flex';

        $this->cache->method('get')->willReturn(null);
        $this->cache->method('set')->with($this->anything());

        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $subject = $this->getMockBuilder(ContentIcon::class)
            ->onlyMethods(['translate'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $subject->method('translate')->willReturnArgument(0);

        $callerClassName = version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '<=')
            ? PageLayoutView::class
            : GridColumnItem::class;

        $icon = $subject->addSubIcon(
            $parameters,
            $this->createStub($callerClassName)
        );
        $this->assertSame('', $icon);
    }

    public function getAddSubIconTestValues(): array
    {
        $formWithoutIcon = $this->createStub(Form::class);
        $formWithIcon = $this->createStub(Form::class);
        $formWithIcon->method('getOption')->with(FormOption::ICON)->willReturn('icon');
        $providerWithoutForm = $this->createStub(ProviderInterface::class);
        $providerWithoutForm->method('getForm')->willReturn(null);
        $providerWithoutForm->method('getGrid')->willReturn(Form\Container\Grid::create());
        $providerWithFormWithoutIcon = $this->createStub(ProviderInterface::class);
        $providerWithFormWithoutIcon->method('getForm')->willReturn($formWithoutIcon);
        $providerWithFormWithoutIcon->method('getGrid')->willReturn(Form\Container\Grid::create());
        $providerWithFormWithIcon = $this->createStub(ProviderInterface::class);
        $providerWithFormWithIcon->method('getForm')->willReturn($formWithIcon);
        $providerWithFormWithIcon->method('getGrid')->willReturn(Form\Container\Grid::create());
        return [
            'no provider' => [['tt_content', 1, []], null],
            'provider without form without field' => [['tt_content', 1, []], $providerWithoutForm],
            'provider without form with field' => [['tt_content', 1, ['field' => 'test']], $providerWithoutForm],
            'provider with form without icon' => [['tt_content', 1, ['field' => 'test']], $providerWithFormWithoutIcon],
            'provider with form with icon' => [['tt_content', 1, ['field' => 'test']], $providerWithFormWithIcon],
        ];
    }

    public function testReturnsEmptyStringWithInvalidCaller(): void
    {
        $subject = new ContentIcon(...$this->getConstructorArguments());
        self::assertSame('', $subject->addSubIcon([], $this));
    }

    public function testReturnsEmptyStringWithInvalidTable(): void
    {
        $callerClassName = version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '<=')
            ? PageLayoutView::class
            : GridColumnItem::class;

        $subject = new ContentIcon(...$this->getConstructorArguments());
        self::assertSame(
            '',
            $subject->addSubIcon(
                ['foo', '', ''],
                $this->createStub($callerClassName)
            )
        );
    }
}
