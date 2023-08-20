<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

abstract class AbstractProviderTestCase extends AbstractTestCase
{
    protected FormDataTransformer $formDataTransformer;
    protected WorkspacesAwareRecordService $recordService;
    protected ViewBuilder $viewBuilder;
    protected CacheService $cacheService;
    protected TypoScriptService $typoScriptService;
    protected string $configurationProviderClassName = Provider::class;
    private array $dummyGridConfiguration = [
        'columns' => [
            [
                'column' => [
                    'name' => 'column1',
                    'label' => 'Label 1',
                    'colPos' => 1,
                ],
            ],
            [
                'column' => [
                    'name' => 'column2',
                    'label' => 'Label 2',
                    'colPos' => 2,
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->formDataTransformer = $this->createStub(FormDataTransformer::class);
        $this->recordService = $this->createStub(WorkspacesAwareRecordService::class);
        $this->viewBuilder = $this->createStub(ViewBuilder::class);
        $this->cacheService = $this->createStub(CacheService::class);
        $this->typoScriptService = $this->createStub(TypoScriptService::class);

        parent::setUp();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->formDataTransformer,
            $this->recordService,
            $this->viewBuilder,
            $this->cacheService,
            $this->typoScriptService,
        ];
    }
}
