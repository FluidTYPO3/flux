<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\Outlet\FormViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class FormViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @return FormViewHelper
     */
    protected function createInstance()
    {
        /** @var AbstractViewHelper $instance */
        $instance = parent::createInstance();
        if (true === method_exists($instance, 'injectConfigurationManager')) {
            $cObject = new ContentObjectRenderer();
            $cObject->start(Records::$contentRecordWithoutParentAndWithoutChildren, 'tt_content');
            /** @var ConfigurationManagerInterface $configurationManager */
            $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();
            $configurationManager->method('getContentObject')->willReturn($cObject);
            $instance->injectConfigurationManager($configurationManager);
        }
        // Note: we initialize the variables here since on LTS, retrieving an unknown variable causes an Exception
        // whereas on 8.x (with Fluid standalone) such unknown variables simply return null.
        $this->viewHelperVariableContainer->add(FormViewHelper::class, 'provider', null);
        $this->viewHelperVariableContainer->add(FormViewHelper::class, 'record', []);
        $this->viewHelperVariableContainer->add(FormViewHelper::class, 'pluginName', 'Content');
        $this->viewHelperVariableContainer->add(FormViewHelper::class, \FluidTYPO3\Flux\ViewHelpers\FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME, 'FluidTYPO3.Flux');

        $instance->setRenderingContext($this->renderingContext);
        $instance->initialize();

        return $instance;
    }

    public function testAddsTableAndUidHiddenFields()
    {
        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['getTableName'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())->method('getTableName')->willReturn('foobar');
        $method = new \ReflectionMethod(FormViewHelper::class, 'renderAdditionalIdentityFields');
        $method->setAccessible(true);
        $subject = $this->createInstance();
        $subject->setRenderingContext($this->renderingContext);
        $providerProperty = new \ReflectionProperty(FormViewHelper::class, 'provider');
        $providerProperty->setAccessible(true);
        $providerProperty->setValue($subject, $provider);
        $recordProperty = new \ReflectionProperty(FormViewHelper::class, 'record');
        $recordProperty->setAccessible(true);
        $recordProperty->setValue($subject, ['uid' => 123]);
        $result = $method->invoke($subject);
        $this->assertStringContainsString('__outlet[table]', $result);
        $this->assertStringContainsString('__outlet[recordUid]', $result);
    }

    public function testRenderMethodAssignsPropertiesFromProvider(): void
    {
        $uriBuilder = $this->getMockBuilder(UriBuilder::class)->setMethods(['uriFor'])->getMock();
        $this->controllerContext->method('getUriBuilder')->willReturn($uriBuilder);
        $uriBuilder->method('uriFor')->willReturn('url');
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();

        GeneralUtility::addInstance(UriBuilder::class, $uriBuilder);

        $tagBuilder = $this->getMockBuilder(TagBuilder::class)->setMethods(['render'])->getMock();
        $tagBuilder->method('render')->willReturn('rendered');

        $this->viewHelperVariableContainer->add(FormViewHelper::class, 'record', ['uid' => 123]);
        $this->viewHelperVariableContainer->add(FormViewHelper::class, 'provider', $provider);

        $subject = $this->getMockBuilder(FormViewHelper::class)
            ->setMethods(
                [
                    'renderChildren',
                    'renderHiddenReferrerFields',
                    'renderTrustedPropertiesField',
                    'getDefaultFieldNamePrefix'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->setRenderingContext($this->renderingContext);
        $subject->method('renderHiddenReferrerFields')->willReturn('');
        $subject->method('renderTrustedPropertiesField')->willReturn('');
        $subject->method('getDefaultFieldNamePrefix')->willReturn('prefix');

        $this->setInaccessiblePropertyValue($subject, 'tag', $tagBuilder);

        $output = $subject->render();

        self::assertSame('rendered', $output);
    }

    public function testThrowsExceptionIfUsedWithoutProvider(): void
    {
        $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'provider', null);
        $this->expectExceptionCode(1669647845);
        $subject = new FormViewHelper();
        $subject->setRenderingContext($this->renderingContext);
        $subject->render();
    }

    public function testThrowsExceptionIfUsedWithoutRecord(): void
    {
        $this->viewHelperVariableContainer->addOrUpdate(
            FormViewHelper::class,
            'provider',
            $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass()
        );
        $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'record', null);
        $this->expectExceptionCode(1669647846);
        $subject = new FormViewHelper();
        $subject->setRenderingContext($this->renderingContext);
        $subject->render();
    }
}
