<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\RenderingContextBuilder;
use FluidTYPO3\Flux\Utility\RequestBuilder;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class RenderingContextBuilderTest extends AbstractTestCase
{
    public function testBuildRenderingContextFor(): void
    {
        if (class_exists(RenderingContextFactory::class)) {
            self::markTestSkipped('Skipping test with RenderingContextFactory dependency');
        }

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['preProcessors'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['expressionNodeTypes'] = [];

        if (class_exists(ControllerContext::class)) {
            $renderingContext = $this->getMockBuilder(RenderingContext::class)
                ->setMethods(['setControllerContext', 'getTemplatePaths', 'setRequest'])
                ->disableOriginalConstructor()
                ->getMock();
            $uriBuilder = $this->getMockBuilder(UriBuilder::class)
                ->disableOriginalConstructor()
                ->getMock();
            $controllerContext = $this->getMockBuilder(ControllerContext::class)
                ->setMethods(['setRequest'])
                ->disableOriginalConstructor()
                ->getMock();
            $configurationManager = $this->getMockBuilder(ConfigurationManager::class)
                ->disableOriginalConstructor()
                ->getMock();
            $requestBuilder = $this->getMockBuilder(RequestBuilder::class)
                ->setMethods(['getEnvironmentVariable'])
                ->disableOriginalConstructor()
                ->getMock();
            $requestBuilder->method('getEnvironmentVariable')->willReturn('foobar');

            GeneralUtility::addInstance(UriBuilder::class, $uriBuilder);
            GeneralUtility::addInstance(ControllerContext::class, $controllerContext);
            GeneralUtility::addInstance(RequestBuilder::class, $requestBuilder);
            GeneralUtility::setSingletonInstance(ConfigurationManager::class, $configurationManager);
        } else {
            $renderingContext = $this->getMockBuilder(RenderingContext::class)
                ->setMethods(['setRequest', 'getTemplatePaths'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)->disableOriginalConstructor()->getMock();
        $renderingContext->method('getTemplatePaths')->willReturn($templatePaths);

        GeneralUtility::addInstance(RenderingContext::class, $renderingContext);

        $subject = new RenderingContextBuilder();
        $output = $subject->buildRenderingContextFor(
            'FluidTYPO3.Flux',
            'Default',
            'default',
            __DIR__ . '/../../Fixtures/Templates/Content/Dummy.html'
        );
        self::assertInstanceOf(RenderingContextInterface::class, $output);
    }
}
