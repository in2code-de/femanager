<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * Get configuration from extension manager
 */
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['femanager']);

/**
 * FE Plugin
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pi1',
	'FE_Manager'
);

/**
 * Include Backend Module
 */
if (TYPO3_MODE === 'BE' && !$confArr['disableModule'] && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'In2.' . $_EXTKEY,
		'web',
		'm1',
		'',
		array(
			'UserBackend' => 'list,userLogout'
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xlf',
		)
	);
}

/**
 * Static TypoScript
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Main', 'Main Settings');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Layout', 'Add Layout CSS');

/**
 * Flexform
 */
$pluginSignature = str_replace('_', '', $_EXTKEY) . '_pi1';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	$pluginSignature,
	'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/FlexFormPi1.xml'
);

/**
 * Load UserFunc for FlexForm Field selection
 */
if (TYPO3_MODE == 'BE') {
	require_once(
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Utility/FlexFormFieldSelection.php'
	);
}

/**
 * Table configuration fe_users
 */
$tempColumns = array (
	'gender' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_user.gender',
		'config' => Array (
			'type' => 'radio',
			'items' => Array (
				Array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_user.gender.item0', '0'),
				Array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_user.gender.item1', '1')
			),
		)
	),
	'date_of_birth' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_user.dateOfBirth',
		'config' => Array (
			'type' => 'input',
			'size' => 10,
			'max' => 20,
			'eval' => 'date',
			'checkbox' => '0',
			'default' => ''
		)
	),
	'crdate' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.crdate',
		'config' => array (
			'type' => 'input',
			'size' => 30,
			'eval' => 'datetime',
			'readOnly' => 1,
			'default' => time()
		)
	),
	'tstamp' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.tstamp',
		'config' => array (
			'type' => 'input',
			'size' => 30,
			'eval' => 'datetime',
			'readOnly' => 1,
			'default' => time()
		)
	),
	'tx_femanager_confirmedbyuser' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.registrationconfirmedbyuser',
		'config' => array (
			'type' => 'check',
			'default' => 0,
		)
	),
	'tx_femanager_confirmedbyadmin' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.registrationconfirmedbyadmin',
		'config' => array (
			'type' => 'check',
			'default' => 0,
		)
	),
);
$fields = 'crdate, tstamp, tx_femanager_confirmedbyuser, tx_femanager_confirmedbyadmin';

if (empty($confArr['disableLog'])) {
	$tempColumns['tx_femanager_log'] = array (
		'exclude' => 1,
		'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.log',
		'config' => array (
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

$tempColumns['tx_femanager_changerequest'] = array (
	'exclude' => 1,
	'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.changerequest',
	'config' => array (
		'type' => 'text',
		'cols' => '40',
		'rows' => '15',
		'wrap' => 'off',
		'readOnly' => 1
	)
);
$fields .= ', tx_femanager_changerequest';

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gender, date_of_birth', '', 'after:name');
if (version_compare(TYPO3_branch, '6.2', '<')) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
} else {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'fe_users',
	'--div--;LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:fe_users.tab;;;;1-1-1, ' . $fields
);


/**
 * Table configuration tx_femanager_domain_model_log
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_femanager_domain_model_log');
$TCA['tx_femanager_domain_model_log'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'default_sortby' => 'ORDER BY crdate DESC',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'title',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Log.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/Log.gif'
	),
);