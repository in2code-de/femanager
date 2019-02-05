<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
    /**
     * Include Backend Module
     */
    if (!\In2code\Femanager\Utility\ConfigurationUtility::isDisableModuleActive() &&
        !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'In2code.femanager',
            'web',
            'm1',
            '',
            [
                'UserBackend' => 'list,confirmation,userLogout,confirmUser,refuseUser,listOpenUserConfirmations,resendUserConfirmationRequest'
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:femanager/Resources/Public/Icons/Extension.svg',
                'labels' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_mod.xlf',
            ]
        );
    }

    /**
     * Add user TSConfig
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:femanager/Configuration/UserTsConfig/BackendModule.typoscript">'
    );
});
