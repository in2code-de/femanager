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
	),
	array(
		 'User' => 'show, fileUpload, fileDelete, validate',
		 'New' => 'create, new, confirmCreateRequest, createStatus',
		 'Edit' => 'edit, update, delete, confirmUpdateRequest',
	)
);

// eID for File Upload
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerFileUpload'] = 'EXT:femanager/Classes/Utility/EidFileUpload.php';

// eID for File Delete
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerFileDelete'] = 'EXT:femanager/Classes/Utility/EidFileDelete.php';

// eID for Field Validation
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerValidate'] = 'EXT:femanager/Classes/Utility/EidValidate.php';
?>