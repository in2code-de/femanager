<?php

use In2code\Femanager\Controller\UserBackendController;
use In2code\Femanager\Utility\ConfigurationUtility;

if (!ConfigurationUtility::isDisableModuleActive()) {
    return [
        'tx_femanager' => [
            'parent' => 'web',
            'position' => ['after' => 'web_info'],
            'access' => 'user',
            'icon' => 'EXT:femanager/Resources/Public/Icons/Extension.svg',
            'path' => '/module/web/femanager',
            'labels' => 'LLL:EXT:femanager/Resources/Private/Language/locallang_mod.xlf',
            'extensionName' => 'Femanager',
            'controllerActions' => [
                UserBackendController::class => [
                    'list',
                    'confirmation',
                    'userLogout',
                    'confirmUser',
                    'refuseUser',
                    'listOpenUserConfirmations',
                    'resendUserConfirmationRequest',
                ],
            ],
        ],
    ];
}
return [];
