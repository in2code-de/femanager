<?php

/**
 * Table configuration fe_users
 */
$feUsersColumns = array(
    'gender' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'tx_femanager_domain_model_user.gender',
        'config' => array(
            'type' => 'radio',
            'items' => array(
                array(
                    'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                    'tx_femanager_domain_model_user.gender.item0',
                    '0'
                ),
                array(
                    'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
                    'tx_femanager_domain_model_user.gender.item1',
                    '1'
                )
            ),
        )
    ),
    'date_of_birth' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'tx_femanager_domain_model_user.dateOfBirth',
        'config' => array(
            'type' => 'input',
            'size' => 10,
            'max' => 20,
            'eval' => 'date',
            'checkbox' => '0',
            'default' => ''
        )
    ),
    'crdate' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.crdate',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'datetime',
            'readOnly' => 1,
            'default' => time()
        )
    ),
    'tstamp' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.tstamp',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'datetime',
            'readOnly' => 1,
            'default' => time()
        )
    ),
    'tx_femanager_confirmedbyuser' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.registrationconfirmedbyuser',
        'config' => array(
            'type' => 'check',
            'default' => 0,
        )
    ),
    'tx_femanager_confirmedbyadmin' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:' .
            'fe_users.registrationconfirmedbyadmin',
        'config' => array(
            'type' => 'check',
            'default' => 0,
        )
    ),
);
$fields = 'crdate, tstamp, tx_femanager_confirmedbyuser, tx_femanager_confirmedbyadmin';

if (!\In2code\Femanager\Utility\ConfigurationUtility::isDisableLogActive()) {
    $feUsersColumns['tx_femanager_log'] = array(
        'exclude' => 1,
        'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.log',
        'config' => array(
            'type' => 'inline',
            'foreign_table' => 'tx_femanager_domain_model_log',
            'foreign_field' => 'user',
            'maxitems' => 1000,
            'minitems' => 0,
            'appearance' => array(
                'collapseAll' => 1,
                'expandSingle' => 1,
            ),
        )
    );
    $fields .= ', tx_femanager_log';
}

$feUsersColumns['tx_femanager_changerequest'] = array(
    'exclude' => 1,
    'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.changerequest',
    'config' => array(
        'type' => 'text',
        'cols' => '40',
        'rows' => '15',
        'wrap' => 'off',
        'readOnly' => 1
    )
);
$fields .= ', tx_femanager_changerequest';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'gender, date_of_birth',
    '',
    'after:name'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $feUsersColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.tab, ' . $fields
);
