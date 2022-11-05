<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\NormalizedData\Converter;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\HookSubscribers\PagePreviewRenderer;
use FluidTYPO3\Flux\Integration\NormalizedData\AbstractImplementation;
use FluidTYPO3\Flux\Integration\NormalizedData\Converter\InlineRecordDataConverter;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractImplementationTest extends AbstractTestCase
{
    public function testAppliesToRecord(): void
    {
        $subject = $this->getMockBuilder(AbstractImplementation::class)
            ->setConstructorArgs([])
            ->getMockForAbstractClass();
        self::assertTrue($subject->appliesToRecord(['uid' => 123]));
    }
}
