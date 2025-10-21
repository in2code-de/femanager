<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(function () {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'Registration',
        [
            \In2code\Femanager\Controller\NewController::class => 'new, create, confirmCreateRequest, createStatus, resendConfirmationMail, resendConfirmationDialogue',
        ],
        [
            \In2code\Femanager\Controller\NewController::class => 'new, create, confirmCreateRequest, createStatus, resendConfirmationMail, resendConfirmationDialogue',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'Edit',
        [
            \In2code\Femanager\Controller\EditController::class => 'edit, update, delete, confirmUpdateRequest',
            \In2code\Femanager\Controller\UserController::class => 'imageDelete',
        ],
        [
            \In2code\Femanager\Controller\EditController::class => 'edit, update, delete, confirmUpdateRequest',
            \In2code\Femanager\Controller\UserController::class => 'imageDelete',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'List',
        [
            \In2code\Femanager\Controller\UserController::class => 'list, show, validate, loginAs, imageDelete',
        ],
        [
            \In2code\Femanager\Controller\UserController::class => 'list, show, validate, loginAs, imageDelete',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'Detail',
        [
            \In2code\Femanager\Controller\UserController::class => 'show',
        ],
        [
            \In2code\Femanager\Controller\UserController::class => 'show',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'Invitation',
        [
            \In2code\Femanager\Controller\InvitationController::class => 'new, create, edit, update, delete, status',
        ],
        [
            \In2code\Femanager\Controller\InvitationController::class => 'new, create, edit, update, delete, status',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'ResendConfirmationMail',
        [
            \In2code\Femanager\Controller\NewController::class => 'resendConfirmationDialogue, resendConfirmationMail, confirmCreateRequest, createStatus',
        ],
        [
            \In2code\Femanager\Controller\NewController::class => 'resendConfirmationDialogue, resendConfirmationMail, confirmCreateRequest, createStatus',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'Data',
        [
            \In2code\Femanager\Controller\DataController::class => 'getStatesForCountry',
        ],
        [
            \In2code\Femanager\Controller\DataController::class => 'getStatesForCountry',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_PLUGIN
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'Validation',
        [
            \In2code\Femanager\Controller\UserController::class => 'validate',
        ],
        [
            \In2code\Femanager\Controller\UserController::class => 'validate',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_PLUGIN
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Femanager',
        'Impersonate',
        [
            \In2code\Femanager\Controller\UserController::class => 'loginAs',
        ],
        [
            \In2code\Femanager\Controller\UserController::class => 'loginAs',
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_PLUGIN
    );

    #$container = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
    #$container->registerImplementation(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class, \In2code\Femanager\Persistence\Generic\Mapper\DataMap::class);

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['femanager_ratelimiter'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['femanager_ratelimiter'] = [];
    }

    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'][1408] = 'EXT:femanager/Resources/Private/Layouts/Mail';
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths'][1408] = 'EXT:femanager/Resources/Private/Partials/Mail';
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1408] = 'EXT:femanager/Resources/Private/Templates/Mail';
});
