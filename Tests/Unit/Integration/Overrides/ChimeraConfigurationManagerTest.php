<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Overrides\ChimeraConfigurationManager;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ChimeraConfigurationManagerTest extends AbstractTestCase
{
    private ContainerInterface $container;
    private FrontendConfigurationManager $frontendConfigurationManager;
    private BackendConfigurationManager $backendConfigurationManager;

    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '<')) {
            self::markTestSkipped('Skipping chimera configuration manager test on v10');
        } elseif (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            self::markTestSkipped('Skipping chimera configuration manager test on v13');
        }

        $this->frontendConfigurationManager = $this->getMockBuilder(FrontendConfigurationManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->backendConfigurationManager = $this->getMockBuilder(BackendConfigurationManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->method('get')->willReturnMap(
            [
                [FrontendConfigurationManager::class, $this->frontendConfigurationManager],
                [BackendConfigurationManager::class, $this->backendConfigurationManager],
                [
                    ExtensionService::class,
                    $this->getMockBuilder(ExtensionService::class)->disableOriginalConstructor()->getMock()
                ],
            ]
        );
    }

    /**
     * @dataProvider getGetConfigurationTestValues
     */
    public function testGetConfigurationDelegatesToActiveConfigurationManager(
        string $configurationType,
        int $requestType,
        bool $expectsException = false
    ): void {
        $GLOBALS['TYPO3_REQUEST'] = $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass();
        $GLOBALS['TYPO3_REQUEST']->method('getAttribute')->with('applicationType')->willReturn($requestType);

        $subject = new ChimeraConfigurationManager($this->container);
        if ($expectsException) {
            self::expectExceptionCode(1206031879);
        } else {
            $method = 'getConfiguration';
            if ($configurationType === ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT) {
                $method = 'getTypoScriptSetup';
            }
            if ($requestType === SystemEnvironmentBuilder::REQUESTTYPE_FE) {
                $this->frontendConfigurationManager->expects(self::once())->method($method)->willReturn([]);
            } else {
                $this->backendConfigurationManager->expects(self::once())->method($method)->willReturn([]);
            }
        }

        $subject->setRequest($GLOBALS['TYPO3_REQUEST']);
        $result = $subject->getConfiguration($configurationType, 'foo', 'bar');

        self::assertSame([], $result);
    }

    public function getGetConfigurationTestValues(): array
    {
        return [
            'exception on invalid configuration type' => ['invalid', SystemEnvironmentBuilder::REQUESTTYPE_FE, true],
            'type framework, frontend request' => [
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                SystemEnvironmentBuilder::REQUESTTYPE_FE,
            ],
            'type settings, frontend request' => [
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                SystemEnvironmentBuilder::REQUESTTYPE_FE,
            ],
            'type typoscript, frontend request' => [
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
                SystemEnvironmentBuilder::REQUESTTYPE_FE,
            ],
            'type framework, backend request' => [
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                SystemEnvironmentBuilder::REQUESTTYPE_BE,
            ],
            'type settings, backend request' => [
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                SystemEnvironmentBuilder::REQUESTTYPE_BE,
            ],
            'type typoscript, backend request' => [
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
                SystemEnvironmentBuilder::REQUESTTYPE_BE,
            ],
        ];
    }

    public function testSetConfigurationDelegates(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass();
        $GLOBALS['TYPO3_REQUEST']->method('getAttribute')
            ->with('applicationType')
            ->willReturn(SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $this->frontendConfigurationManager->expects(self::exactly(2))->method('setConfiguration')->with(['foo']);

        $subject = new ChimeraConfigurationManager($this->container);
        $subject->setConfiguration(['foo']);
    }

    public function testSetContentObjectDelegates(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass();
        $GLOBALS['TYPO3_REQUEST']->method('getAttribute')
            ->with('applicationType')
            ->willReturn(SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $contentObject = $this->getMockBuilder(ContentObjectRenderer::class)->disableOriginalConstructor()->getMock();
        $this->frontendConfigurationManager->expects(self::exactly(2))
            ->method('setContentObject')
            ->with($contentObject);

        $subject = new ChimeraConfigurationManager($this->container);
        $subject->setContentObject($contentObject);
    }
}
