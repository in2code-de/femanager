<?php

declare(strict_types=1);

return [
    'frontend' => [
        'in2code/femanager/clean-user-group' => [
            'target' => \In2code\Femanager\Middleware\CleanUserGroupMiddleware::class,
            'before' => [
                'typo3/cms-frontend/tsfe',
            ],
            'after' => [
                'typo3/cms-frontend/page-argument-validator',
            ],
        ],
    ]
];
