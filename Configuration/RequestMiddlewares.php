<?php
return [
    'frontend' => [
        'fluidtypo3/flux/request-availability' => [
            'target' => \FluidTYPO3\Flux\Integration\MiddleWare\RequestAvailability::class,
            'before' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
        ],
    ],
];
