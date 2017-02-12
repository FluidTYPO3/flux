<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\ClipBoardUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

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
        $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = array();
    }

    /**
     * @return array
     */
    protected function getClipBoardDataFixture()
    {
        $clipBoardData = array(
            'current' => 'normal',
            'normal' => array(
                'el' => Records::$contentRecordWithoutParentAndWithoutChildren
            )
        );
        return $clipBoardData;
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
    public function canCreateIconWithUrl()
    {
        $GLOBALS['BE_USER'] = $this->getMockBuilder('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication')->getMock();
        $clipBoardData = $this->getClipBoardDataFixture();
        ClipBoardUtility::setClipBoardData($clipBoardData);
        $iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3');
        $this->assertNotEmpty($iconWithUrl);
        ClipBoardUtility::clearClipBoardData();
        unset($GLOBALS['BE_USER']);
    }

    /**
     * @test
     */
    public function canCreateIconWithUrlAsReference()
    {
        $GLOBALS['BE_USER'] = $this->getMockBuilder('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication')->getMock();
        $clipBoardData = $this->getClipBoardDataFixture();
        $clipBoardData['normal']['mode'] = 'reference';
        ClipBoardUtility::setClipBoardData($clipBoardData);
        $iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', true);
        $this->assertNotEmpty($iconWithUrl);
        ClipBoardUtility::clearClipBoardData();
        unset($GLOBALS['BE_USER']);
    }

    /**
     * @test
     */
    public function canCreateIconWithUrlAsReferenceReturnsEmptyStringIfModeIsCut()
    {
        $GLOBALS['BE_USER'] = $this->getMockBuilder('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication')->getMock();
        $clipBoardData = $this->getClipBoardDataFixture();
        ClipBoardUtility::setClipBoardData($clipBoardData);
        $iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', true);
        $this->assertIsString($iconWithUrl);
        $this->assertEmpty($iconWithUrl);
        ClipBoardUtility::clearClipBoardData();
        unset($GLOBALS['BE_USER']);
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
     * @dataProvider getGenerateUniqueIntegerForFluxAreaTestValues
     * @param integer $uid
     * @param string $name
     * @param integer $expected
     */
    public function testGenerateUniqueIntegerForFluxArea($uid, $name, $expected)
    {
        $result = MiscellaneousUtility::generateUniqueIntegerForFluxArea($uid, $name);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getGenerateUniqueIntegerForFluxAreaTestValues()
    {
        return array(
            array(1, 'test', 18630),
            array(321, 'foobar', 19135),
            array(8, 'xyzbazbar', 19178),
            array(123, 'verylongstringverylongstringverylongstring', 22951)
        );
    }

    /**
     * @test
     */
    public function testCreateIcon()
    {
        $graphicsClassName = 'TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$graphicsClassName]['className'] =
            'FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\DummyGraphicalFunctions';
        $this->assertEquals('icon-b7c9dc75b2c29a9a52e8c1f7a996348b', MiscellaneousUtility::createIcon('foobar-icon', 1, 2));
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
