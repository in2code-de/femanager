<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(function () {
    /**
     * Add user TSConfig
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:femanager/Configuration/UserTsConfig/BackendModule.typoscript">'
    );
});
