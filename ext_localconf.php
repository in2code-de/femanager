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
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerFileUpload'] = 'EXT:femanager/Classes/Utility/Eid/FileUpload.php';

// eID for File Delete (FE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerFileDelete'] = 'EXT:femanager/Classes/Utility/Eid/FileDelete.php';

// eID for Field Validation (FE)
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerValidate'] = 'EXT:femanager/Classes/Utility/Eid/Validate.php';