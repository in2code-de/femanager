<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'In2code.femanager',
        'Pi1',
        [
            'User' => 'list, show, validate, loginAs, imageDelete',
            'New' => 'new, create, confirmCreateRequest, createStatus, resendConfirmationMail, resendConfirmationDialogue',
            'Edit' => 'edit, update, delete, confirmUpdateRequest',
            'Invitation' => 'new, create, edit, update, delete, status',
            'Data' => 'getStatesForCountry'
        ],
        [
            'User' => 'list, show, validate, loginAs, imageDelete',
            'New' => 'new, create, confirmCreateRequest, createStatus, resendConfirmationMail, resendConfirmationDialogue',
            'Edit' => 'edit, update, delete, confirmUpdateRequest',
            'Invitation' => 'new, create, edit, update, delete',
            'Data' => 'getStatesForCountry'
        ]
    );

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['femanager_ratelimiter'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['femanager_ratelimiter'] = array();
    }

});
