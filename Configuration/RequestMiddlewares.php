<?php

declare(strict_types=1);

use In2code\Femanager\Middleware\CleanUserGroupMiddleware;
use In2code\Femanager\Middleware\RemovePasswordIfEmptyMiddleware;

return [
    'frontend' => [
        'in2code/femanager/clean-user-group' => [
            'target' => CleanUserGroupMiddleware::class,
            'before' => [
                'typo3/cms-frontend/tsfe',
            ],
            'after' => [
                'typo3/cms-frontend/page-argument-validator',
            ],
        ],
        'in2code/femanager/remove-passowrd-if-empty' => [
            'target' => RemovePasswordIfEmptyMiddleware::class,
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];
