<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'In2.' . $_EXTKEY,
	'Pi1',
	array(
		'User' => 'list, show, fileUpload, fileDelete, validate',
		'New' => 'create, new, confirmCreateRequest, createStatus',
		'Edit' => 'edit, update, delete, confirmUpdateRequest',
		'Invitation' => 'new, create, edit, update, delete, status'
	),
	array(
		'User' => 'list, fileUpload, fileDelete, validate',
		'New' => 'create, new, confirmCreateRequest, createStatus',
		'Edit' => 'edit, update, delete, confirmUpdateRequest',
		'Invitation' => 'new, create, edit, update, delete'
	)
);

// eID for File Upload (FE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerFileUpload'] = 'EXT:femanager/Classes/Utility/EidFileUpload.php';

// eID for File Delete (FE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerFileDelete'] = 'EXT:femanager/Classes/Utility/EidFileDelete.php';

// eID for Field Validation (FE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerValidate'] = 'EXT:femanager/Classes/Utility/EidValidate.php';

// eID for User hide (BE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerUserHide'] = 'EXT:femanager/Classes/Utility/EidUserHide.php';

// eID for User unhide (BE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerUserUnhide'] = 'EXT:femanager/Classes/Utility/EidUserUnhide.php';

// eID for User delete (BE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerUserDelete'] = 'EXT:femanager/Classes/Utility/EidUserDelete.php';