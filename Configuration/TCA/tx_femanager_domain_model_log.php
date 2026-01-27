<?php

use In2code\Femanager\Domain\Model\Log;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'default_sortby' => 'ORDER BY crdate DESC',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title',
        'iconfile' => 'EXT:femanager/Resources/Public/Icons/Log.png',
    ],
    'types' => [
        '1' => [
            'showitem' => 'title, crdate, state, user, ' .
                '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,sys_language_uid, ' .
                'l10n_parent, l10n_diffsource, hidden, starttime, endtime',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'tx_femanager_domain_model_log',
                'foreign_table_where' => 'AND tx_femanager_domain_model_log.pid = ###CURRENT_PID### AND ' .
                    'tx_femanager_domain_model_log.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'format' => 'datetime',
                'renderType' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'format' => 'datetime',
                'renderType' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                'tx_femanager_domain_model_log.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'crdate' => [
            'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                'tx_femanager_domain_model_log.crdate',
            'config' => [
                'type' => 'input',
                'format' => 'datetime',
                'renderType' => 'datetime',
                'eval' => 'datetime,int',
                'readOnly' => true,
            ],
        ],
        'state' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                'tx_femanager_domain_model_log.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.100',
                        'value' => '--div--',
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.101',
                        'value' => Log::STATUS_NEWREGISTRATION,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.102',
                        'value' => Log::STATUS_REGISTRATIONCONFIRMEDUSER,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.103',
                        'value' => Log::STATUS_REGISTRATIONCONFIRMEDADMIN,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.104',
                        'value' => Log::STATUS_REGISTRATIONREFUSEDUSER,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.105',
                        'value' => Log::STATUS_REGISTRATIONREFUSEDADMIN,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.106',
                        'value' => Log::STATUS_PROFILECREATIONREQUEST,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.200',
                        'value' => '--div--',
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.201',
                        'value' => Log::STATUS_PROFILEUPDATED,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.202',
                        'value' => Log::STATUS_PROFILEUPDATECONFIRMEDADMIN,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.203',
                        'value' => Log::STATUS_PROFILEUPDATEREFUSEDADMIN,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.204',
                        'value' => Log::STATUS_PROFILEUPDATEREQUEST,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.205',
                        'value' => Log::STATUS_PROFILEUPDATEREFUSEDSECURITY,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.206',
                        'value' => Log::STATUS_PROFILEUPDATEIMAGEDELETE,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.207',
                        'value' => Log::STATUS_PROFILEUPDATEATTEMPTEDSPOOF,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.300',
                        'value' => '--div--',
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.301',
                        'value' => Log::STATUS_PROFILEDELETE,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.400',
                        'value' => '--div--',
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.401',
                        'value' => Log::STATUS_INVITATIONPROFILECREATED,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.402',
                        'value' => Log::STATUS_INVITATIONPROFILEDELETEDUSER,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.403',
                        'value' => Log::STATUS_INVITATIONHASHERROR,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.404',
                        'value' => Log::STATUS_INVITATIONRESTRICTEDPAGE,
                    ],
                    [
                        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                        'tx_femanager_domain_model_log.state.405',
                        'value' => Log::STATUS_INVITATIONPROFILEENABLED,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'user' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
