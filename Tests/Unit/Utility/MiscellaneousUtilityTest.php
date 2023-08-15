<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyGraphicalFunctions;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Backend\Routing\Exception\ResourceNotFoundException;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MiscellaneousUtilityTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mocking the singleton of IconRegistry is apparently required for unit tests to work on some environments.
        // Since it doesn't matter much what this method actually responds for these tests, we mock it for all envs.
        $iconRegistryMock = $this->getMockBuilder(IconRegistry::class)
            ->onlyMethods(['isRegistered', 'getIconConfigurationByIdentifier'])
            ->disableOriginalConstructor()
            ->getMock();
        $iconRegistryMock->expects($this->any())->method('isRegistered')->willReturn(true);
        $iconRegistryMock->expects($this->any())->method('getIconConfigurationByIdentifier')->willReturn([
            'provider' => SvgIconProvider::class,
            'options' => [
                'source' => 'EXT:core/Resources/Public/Icons/T3Icons/default/default-not-found.svg'
            ]
        ]);
        GeneralUtility::setSingletonInstance(IconRegistry::class, $iconRegistryMock);
        $router = $this->getMockBuilder(Router::class)->disableOriginalConstructor()->getMock();
        try {
            $router->match('tce_db');
        } catch (ResourceNotFoundException $error) {
            $router->addRoute('tce_db', new Route('tce_db', []));
        }
    }

    protected function tearDown(): void
    {
        GeneralUtility::removeSingletonInstance(IconRegistry::class, GeneralUtility::makeInstance(IconRegistry::class));
    }

    protected function getFormOptionsFixture(): array
    {
        $formOptionsData = [
            'extensionName' => 'flux',
            'iconOption' => 'Icons/Mock/Fixture.gif',
        ];
        return $formOptionsData;
    }

    protected function getFormInstance(): Form
    {
        return Form::create();
    }

    /**
     * @test
     */
    public function canGetIconForTemplateIfIconOptionIsSet(): void
    {
        $formOptionsFixture = $this->getFormOptionsFixture();
        /** @var Form $form */
        $form = $this->getFormInstance();
        $form->setOption(FormOption::ICON, $formOptionsFixture['iconOption']);
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        $this->assertEquals($formOptionsFixture['iconOption'], $icon);
    }

    /**
     * @test
     */
    public function returnFalseResultForGivenTemplateButNoTemplateIconIsFound(): void
    {
        $package = $this->getMockBuilder(Package::class)
            ->onlyMethods(['getPackagePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $package->method('getPackagePath')->willReturn('.');

        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->onlyMethods(['isPackageActive', 'getPackage'])
            ->disableOriginalConstructor()
            ->getMock();
        $packageManager->method('isPackageActive')->willReturn(true);
        $packageManager->method('getPackage')->willReturn($package);

        AccessibleExtensionManagementUtility::setPackageManager($packageManager);

        $formOptionsFixture = $this->getFormOptionsFixture();
        $mockExtensionUrl = $this->getMockExtension();
        /** @var Form $form */
        $form = $this->getFormInstance();
        $form->setOption(
            FormOption::TEMPLATE_FILE,
            $mockExtensionUrl .
            '/' .
            $formOptionsFixture['extensionName'] .
            '/Resources/Private/Templates/Content/TestFalse.html'
        );
        $form->setExtensionName($formOptionsFixture['extensionName']);
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        $this->assertNull($icon);
    }

    /**
     * @test
     */
    public function returnFalseResultIfNoTemplateAndNoIconOptionIsSet(): void
    {
        $form = $this->getFormInstance();
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        $this->assertNull($icon);
    }

    /**
     * @test
     */
    public function testCreateIcon(): void
    {
        $graphicsClassName = 'TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$graphicsClassName]['className'] =
            'FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\DummyGraphicalFunctions';
        $this->assertEquals('foobar-icon', MiscellaneousUtility::createIcon('foobar-icon'));
    }

    /**
     * @test
     */
    public function testCreateIconWithCustomIdentifier(): void
    {
        $graphicsClassName = GraphicalFunctions::class;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$graphicsClassName]['className'] =
            DummyGraphicalFunctions::class;
        $this->assertEquals('foobar-icon', MiscellaneousUtility::createIcon('foobar-icon', 'icon-identifier'));
    }

    protected function getMockExtension(): string
    {
        $structure = [
            'flux' => [
                'Resources' => [
                    'Private' => [
                        'Templates' => [
                            'Content' => [
                                'TestTrue.html' => 'Test template with Icon available',
                                'TestFalse.html' => 'Test template with Icon not available'
                            ]
                        ]
                    ],
                    'Public' => [
                        'Icons' => [
                            'Content' => [
                                'TestTrue.png' => 'Test-Icon'
                            ]
                        ]
                    ]
                ],
            ]
        ];
        vfsStream::setup('ext', null, $structure);
        $vfsUrl = vfsStream::url('ext');

        return $vfsUrl;
    }

    /**
     * @dataProvider getCleanFlexFormXmlTestValues
     */
    public function testCleanFlexFormXml(string $xml, array $removals, string $expected): void
    {
        $result = MiscellaneousUtility::cleanFlexFormXml($xml, $removals);
        $this->assertEquals($expected, $result);
    }

    public function getCleanFlexFormXmlTestValues(): array
    {
        return [
            ['<data><fields></fields></data>', [], ''],
            ['<data><field index="columns">   <el index="el">   </el>   </field></data>', [], ''],
            ['
                <data>
                    <sheet index="emptySheet1"></sheet>
                    <sheet index="emptySheet2"></sheet>
                    <sheet index="emptySheet3"></sheet>
                </data>',
                [],
                ''
            ],
        ];
    }
}
