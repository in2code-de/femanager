<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_femanager_domain_model_log'] = array(
	'ctrl' => $TCA['tx_femanager_domain_model_log']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, crdate, state, user',
	),
	'types' => array(
		'1' => array(
			'showitem' =>
				'title, crdate, state, user,
				--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,sys_language_uid;;;;1-1-1,
				l10n_parent, l10n_diffsource, hidden;;1, starttime, endtime'
		),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_femanager_domain_model_log',
				'foreign_table_where' =>
					'AND tx_femanager_domain_model_log.pid = ###CURRENT_PID### AND tx_femanager_domain_model_log.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'crdate' => array(
			'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.crdate',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'datetime',
				'readOnly' => 1
			),
		),
		'state' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state',
			'config' => array(
				'type' => 'select',
				'items' => array (
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.100', '--div--'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.101', '101'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.102', '102'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.103', '103'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.104', '104'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.105', '105'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.106', '106'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.200', '--div--'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.201', '201'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.202', '202'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.203', '203'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.204', '204'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.205', '205'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.300', '--div--'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.301', '301'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.400', '--div--'),
					array('LLL:EXT:femanager/Resources/Private/Language/locallang_db.xlf:tx_femanager_domain_model_log.state.401', '401'),
				),
				'size' => 1,
				'maxitems' => 1
			),
		),
		'user' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
	),
);