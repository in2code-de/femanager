<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'femanager',
        'Pi1',
        [
            \In2code\Femanager\Controller\UserController::class => 'list, show, validate, loginAs, imageDelete',
            \In2code\Femanager\Controller\NewController::class => 'new, create, confirmCreateRequest, createStatus, resendConfirmationMail, resendConfirmationDialogue',
            \In2code\Femanager\Controller\EditController::class => 'edit, update, delete, confirmUpdateRequest',
            \In2code\Femanager\Controller\InvitationController::class => 'new, create, edit, update, delete, status',
            \In2code\Femanager\Controller\DataController::class => 'getStatesForCountry'
        ],
        [
            \In2code\Femanager\Controller\UserController::class => 'list, show, validate, loginAs, imageDelete',
            \In2code\Femanager\Controller\NewController::class => 'new, create, confirmCreateRequest, createStatus, resendConfirmationMail, resendConfirmationDialogue',
            \In2code\Femanager\Controller\EditController::class => 'edit, update, delete, confirmUpdateRequest',
            \In2code\Femanager\Controller\InvitationController::class => 'new, create, edit, update, delete',
            \In2code\Femanager\Controller\DataController::class => 'getStatesForCountry'
        ]
    );

    $container = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
    // $container->registerImplementation(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class, \In2code\Femanager\Persistence\Generic\Mapper\DataMap::class);

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['femanager_ratelimiter'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['femanager_ratelimiter'] = [];
    }
});
