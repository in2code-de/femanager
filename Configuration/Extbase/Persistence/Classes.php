<?php

declare(strict_types=1);

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;

return [
    User::class => [
        'tableName' => User::TABLE_NAME,
        'properties' => [
            'terms' => [
                'fieldName' => 'tx_femanager_terms'
            ],
            'termsDateOfAcceptance' => [
                'fieldName' => 'tx_femanager_terms_date_of_acceptance'
            ]
        ]
    ],
    UserGroup::class => [
        'tableName' => \In2code\Femanager\Domain\Model\UserGroup::TABLE_NAME
    ]
];
