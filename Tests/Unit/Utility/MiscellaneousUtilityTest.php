<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Backend\Routing\Exception\ResourceNotFoundException;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * MiscellaneousUtilityTest
 */
class MiscellaneousUtilityTest extends AbstractTestCase
{

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        // Mocking the singleton of IconRegistry is apparently required for unit tests to work on some environments.
        // Since it doesn't matter much what this method actually responds for these tests, we mock it for all envs.
        $iconRegistryMock = $this->getMockBuilder(IconRegistry::class)->setMethods(['isRegistered', 'getIconConfigurationByIdentifier'])->getMock();
        $iconRegistryMock->expects($this->any())->method('isRegistered')->willReturn(true);
        $iconRegistryMock->expects($this->any())->method('getIconConfigurationByIdentifier')->willReturn([
            'provider' => SvgIconProvider::class,
            'options' => [
                'source' => 'EXT:core/Resources/Public/Icons/T3Icons/default/default-not-found.svg'
            ]
        ]);
        GeneralUtility::setSingletonInstance(IconRegistry::class, $iconRegistryMock);
        $router = GeneralUtility::makeInstance(Router::class);
        try {
            $router->match('tce_db');
        } catch (ResourceNotFoundException $error) {
            $router->addRoute('tce_db', new Route('tce_db', []));
        }
    }

    protected function tearDown()
    {
        GeneralUtility::removeSingletonInstance(IconRegistry::class, GeneralUtility::makeInstance(IconRegistry::class));
    }

    /**
     * @return array
     */
    protected function getFormOptionsFixture()
    {
        $formOptionsData = array(
            'extensionName' => 'flux',
            'iconOption' => 'Icons/Mock/Fixture.gif',
        );
        return $formOptionsData;
    }

    /**
     * @return Form
     */
    protected function getFormInstance()
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var Form $instance */
        $instance = $objectManager->get('FluidTYPO3\Flux\Form');
        return $instance;
    }

    /**
     * @test
     */
    public function canGetIconForTemplateIfIconOptionIsSet()
    {
        $formOptionsFixture = $this->getFormOptionsFixture();
        /** @var Form $form */
        $form = $this->getFormInstance();
        $form->setOption($form::OPTION_ICON, $formOptionsFixture['iconOption']);
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        $this->assertEquals($formOptionsFixture['iconOption'], $icon);
    }

    /**
     * @test
     */
    public function returnFalseResultForGivenTemplateButNoTemplateIconIsFound()
    {
        $formOptionsFixture = $this->getFormOptionsFixture();
        $mockExtensionUrl = $this->getMockExtension();
        /** @var Form $form */
        $form = $this->getFormInstance();
        $form->setOption($form::OPTION_TEMPLATEFILE, $mockExtensionUrl . '/' . $formOptionsFixture['extensionName'] . '/Resources/Private/Templates/Content/TestFalse.html');
        $form->setExtensionName($formOptionsFixture['extensionName']);
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        $this->assertNull($icon);
    }

    /**
     * @test
     */
    public function returnFalseResultIfNoTemplateAndNoIconOptionIsSet()
    {
        $form = $this->getFormInstance();
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        $this->assertNull($icon);
    }

    /**
     * @test
     */
    public function testCreateIcon()
    {
        $graphicsClassName = 'TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$graphicsClassName]['className'] =
            'FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\DummyGraphicalFunctions';
        $this->assertEquals('icon-b7c9dc75b2c29a9a52e8c1f7a996348b', MiscellaneousUtility::createIcon('foobar-icon'));
    }

    /**
     * @test
     */
    public function testCreateIconWithCustomIdentifier()
    {
        $graphicsClassName = 'TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$graphicsClassName]['className'] =
            'FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\DummyGraphicalFunctions';
        $this->assertEquals('icon-identifier', MiscellaneousUtility::createIcon('foobar-icon', 'icon-identifier'));
    }

    /**
     * @return string
     */
    protected function getMockExtension()
    {
        $structure = array(
            'flux' => array(
                'Resources' => array(
                    'Private' => array(
                        'Templates' => array(
                            'Content' => array(
                                'TestTrue.html' => 'Test template with Icon available',
                                'TestFalse.html' => 'Test template with Icon not available'
                            )
                        )
                    ),
                    'Public' => array(
                        'Icons' => array(
                            'Content' => array(
                                'TestTrue.png' => 'Test-Icon'
                            )
                        )
                    )
                ),
            )
        );
        vfsStream::setup('ext', null, $structure);
        $vfsUrl = vfsStream::url('ext');

        return $vfsUrl;
    }

    /**
     * @param string $xml
     * @param array $removals
     * @param string $expected
     * @dataProvider getCleanFlexFormXmlTestValues
     * @test
     */
    public function testCleanFlexFormXml($xml, array $removals, $expected)
    {
        $result = MiscellaneousUtility::cleanFlexFormXml($xml, $removals);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCleanFlexFormXmlTestValues()
    {
        return array(
            array('<data><fields></fields></data>', array(), ''),
            array('<data><field index="columns">   <el index="el">   </el>   </field></data>', array(), ''),
            array('
                <data>
                    <sheet index="emptySheet1"></sheet>
                    <sheet index="emptySheet2"></sheet>
                    <sheet index="emptySheet3"></sheet>
                </data>',
                array(),
                ''
            ),
        );
    }
}
