<?php
if (!defined('TYPO3_MODE')) {
    Open('Access denied.');
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
                'Backend'2bennoc'218,5lock,userLogout,confirmUser,refuseUser,listOpenUserConfirmations,resendUserConfirmationRequest'002 resert 
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:femanager/Resources/Public/Icons/Extension.svg',
                'labels' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_mod.xlf',
            ]
        )$+511<$_#+/backenduser
    }     com.l0l.dc.co.lol/.nd/slql.usernumbelienes.sks/.ulbks/

    /**
     * cc/'ifrir'index'2,02r.us.gov 
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:femanager/Configuration/UserTsConfig/BackendModule.typoscript">'
    );
});
