<?php

/**
 * Table configuration fe_users
 */
$feUsersColumns = [
    'gender' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'tx_femanager_domain_model_user.gender',
        'config' => [
            'type' => 'radio',
            'items' => [
                [
                    'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                    'tx_femanager_domain_model_user.gender.item0',
                    '0'
                ],
                [
                    'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                    'tx_femanager_domain_model_user.gender.item1',
                    '1'
                ],
                [
                    'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                    'tx_femanager_domain_model_user.gender.item2',
                    '2'
                ]
            ],
        ]
    ],
    'date_of_birth' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'tx_femanager_domain_model_user.dateOfBirth',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'size' => 10,
            'eval' => 'date',
            'checkbox' => '0',
            'default' => 0
        ]
    ],
    'crdate' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.crdate',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'size' => 30,
            'eval' => 'datetime',
            'readOnly' => true,
            'default' => time()
        ]
    ],
    'tstamp' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.tstamp',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'size' => 30,
            'eval' => 'datetime',
            'readOnly' => true,
            'default' => time()
        ]
    ],
    'tx_femanager_confirmedbyuser' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.registrationconfirmedbyuser',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ]
    ],
    'tx_femanager_confirmedbyadmin' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.registrationconfirmedbyadmin',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ]
    ],
    'tx_femanager_terms' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.terms',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ]
    ],
    'tx_femanager_terms_date_of_acceptance' => [
        'displayCond' => 'FIELD:tx_femanager_terms:REQ:TRUE',
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.terms_date_of_acceptance',
        'exclude' => true,
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'size' => 30,
            'eval' => 'datetime',
            'readOnly' => true,
        ]
    ],
];

$staticInfoTablesIsLoaded = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables');
if ($staticInfoTablesIsLoaded) {
    $feUsersColumns['state'] = [
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'tx_femanager_domain_model_user.state',
        'exclude' => true,
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:pleaseChoose', '']
            ],
            'itemsProcFunc' => 'In2code\\Femanager\\UserFunc\\StaticInfoTables->getStatesOptions',
            'maxitems' => 1,
        ]
    ];
}
$extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
);
if ($extConf->get('femanager', 'overrideFeUserCountryFieldWithSelect')) {
    $GLOBALS['TCA']['fe_users']['columns']['country']['config'] = [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => 'In2code\\Femanager\\UserFunc\\StaticInfoTables->getCountryOptions',
        'items' => [
            ['LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:pleaseChoose', '']
        ],
        'maxitems' => 1,
    ];
}

$fields = 'crdate, tstamp, tx_femanager_confirmedbyuser, tx_femanager_confirmedbyadmin, tx_femanager_terms, ' .
    'tx_femanager_terms_date_of_acceptance';

if (!\In2code\Femanager\Utility\ConfigurationUtility::isDisableLogActive()) {
    $feUsersColumns['tx_femanager_log'] = [
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.log',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_femanager_domain_model_log',
            'foreign_field' => 'user',
            'maxitems' => 1000,
            'minitems' => 0,
            'appearance' => [
                'collapseAll' => 1,
                'expandSingle' => 1,
            ],
        ]
    ];
    $fields .= ', tx_femanager_log';
}

$feUsersColumns['tx_femanager_changerequest'] = [
    'exclude' => 1,
    'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.changerequest',
    'config' => [
        'type' => 'text',
        'cols' => '40',
        'rows' => '15',
        'wrap' => 'off',
        'readOnly' => 1
    ]
];
$fields .= ', tx_femanager_changerequest';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'gender, date_of_birth',
    '',
    'after:name'
);
if ($staticInfoTablesIsLoaded) {
    $GLOBALS['TCA']['fe_users']['columns']['country']['onChange'] = 'reload';
    $fields .= ',state';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'state',
        '',
        'after:country'
    );
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $feUsersColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.tab, ' . $fields
);
