<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use FluidTYPO3\Flux\ViewHelpers\Outlet\FormViewHelper;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * FormViewHelperTest
 */
class FormViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @return FormViewHelper
     */
    protected function createInstance()
    {
        $className = $this->getViewHelperClassName();
        /** @var AbstractViewHelper $instance */
        $instance = $this->objectManager->get($className);
        if (true === method_exists($instance, 'injectConfigurationManager')) {
            $cObject = new ContentObjectRenderer();
            $cObject->start(Records::$contentRecordWithoutParentAndWithoutChildren, 'tt_content');
            /** @var ConfigurationManagerInterface $configurationManager */
            $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
            $configurationManager->setContentObject($cObject);
            $instance->injectConfigurationManager($configurationManager);
        }
        $context = $this->objectManager->get(RenderingContext::class);
        // Note: we initialize the variables here since on LTS, retrieving an unknown variable causes an Exception
        // whereas on 8.x (with Fluid standalone) such unknown variables simply return null.
        $context->getViewHelperVariableContainer()->add(FormViewHelper::class, 'provider', null);
        $context->getViewHelperVariableContainer()->add(FormViewHelper::class, 'record', []);
        $context->getViewHelperVariableContainer()->add(FormViewHelper::class, 'pluginName', 'Content');
        $context->getViewHelperVariableContainer()->add(FormViewHelper::class, \FluidTYPO3\Flux\ViewHelpers\FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME, 'FluidTYPO3.Flux');
        $instance->setRenderingContext($context);
        $instance->initialize();
        return $instance;
    }

    /**
     * @test
     */
    public function testAddsTableAndUidHiddenFields()
    {
        $provider = $this->getMockBuilder(Provider::class)->setMethods(['getTableName'])->getMock();
        $provider->expects($this->once())->method('getTableName')->willReturn('foobar');
        $method = new \ReflectionMethod(FormViewHelper::class, 'renderAdditionalIdentityFields');
        $method->setAccessible(true);
        $subject = $this->objectManager->get(FormViewHelper::class);
        $subject->setRenderingContext($this->objectManager->get(RenderingContext::class));
        $providerProperty = new \ReflectionProperty(FormViewHelper::class, 'provider');
        $providerProperty->setAccessible(true);
        $providerProperty->setValue($subject, $provider);
        $recordProperty = new \ReflectionProperty(FormViewHelper::class, 'record');
        $recordProperty->setAccessible(true);
        $recordProperty->setValue($subject, ['uid' => 123]);
        $result = $method->invoke($subject);
        $this->assertContains('__outlet[table]', $result);
        $this->assertContains('__outlet[recordUid]', $result);
    }
}
