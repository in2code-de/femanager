<?php

declare(strict_types = 1);

return [
    \In2code\Femanager\Domain\Model\User::class => [
        'tableName' => \In2code\Femanager\Domain\Model\User::TABLE_NAME,
        'properties' => [
            'terms' => [
                'fieldName' => 'tx_femanager_terms'
            ],
            'termsDateOfAcceptance' => [
                'fieldName' => 'tx_femanager_terms_date_of_acceptance'
            ]
        ]
    ],
    \In2code\Femanager\Domain\Model\UserGroup::class => [
        'tableName' => 'fe_groups'
    ]
];
