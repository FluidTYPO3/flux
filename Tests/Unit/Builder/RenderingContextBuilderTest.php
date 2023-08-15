<?php
namespace FluidTYPO3\Flux\Tests\Unit\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class RenderingContextBuilderTest extends AbstractTestCase
{
    public function testBuildRenderingContextFor(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        if (class_exists(ExtbaseRequestParameters::class)) {
            $extbaseParameters = $this->getMockBuilder(ExtbaseRequestParameters::class)->getMock();
            $request->method('getAttribute')->with('extbase')->willReturn($extbaseParameters);
        }


        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['preProcessors'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['expressionNodeTypes'] = [];
        $requestBuilder = $this->getMockBuilder(RequestBuilder::class)
            ->onlyMethods(['getEnvironmentVariable', 'getServerRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestBuilder->method('getEnvironmentVariable')->willReturn('foobar');
        $requestBuilder->method('getServerRequest')->willReturn($request);

        if (class_exists(ControllerContext::class)) {
            $renderingContext = $this->getMockBuilder(RenderingContext::class)
                ->onlyMethods(['setControllerContext', 'getTemplatePaths', 'setRequest'])
                ->disableOriginalConstructor()
                ->getMock();
            $uriBuilder = $this->getMockBuilder(UriBuilder::class)
                ->disableOriginalConstructor()
                ->getMock();
            $controllerContext = $this->getMockBuilder(ControllerContext::class)
                ->setMethods(['setRequest'])
                ->disableOriginalConstructor()
                ->getMock();

            GeneralUtility::addInstance(UriBuilder::class, $uriBuilder);
            GeneralUtility::addInstance(ControllerContext::class, $controllerContext);
        } else {
            $renderingContext = $this->getMockBuilder(RenderingContext::class)
                ->onlyMethods(['setRequest', 'getTemplatePaths'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)->disableOriginalConstructor()->getMock();
        $renderingContext->method('getTemplatePaths')->willReturn($templatePaths);

        $subject = $this->getMockBuilder(RenderingContextBuilder::class)
            ->onlyMethods(['createRenderingContextInstance'])
            ->setConstructorArgs([$requestBuilder])
            ->getMock();
        $subject->method('createRenderingContextInstance')->willReturn($renderingContext);

        $output = $subject->buildRenderingContextFor(
            'FluidTYPO3.Flux',
            'Default',
            'default',
            'Default',
            __DIR__ . '/../../Fixtures/Templates/Content/Dummy.html'
        );
        self::assertInstanceOf(RenderingContextInterface::class, $output);
    }
}
