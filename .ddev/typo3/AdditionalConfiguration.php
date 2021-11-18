<?php

if (getenv('IS_DDEV_PROJECT') == 'true') {
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
        $GLOBALS['TYPO3_CONF_VARS'],
        [
            'DB' => [
                'Connections' => [
                    'Default' => [
                        'dbname' => 'db',
                        'host' => 'db',
                        'password' => 'db',
                        'port' => '3306',
                        'user' => 'db',
                    ],
                ],
            ],
            // This GFX configuration allows processing by installed ImageMagick 6
            'GFX' => [
                'processor' => 'ImageMagick',
                'processor_path' => '/usr/bin/',
                'processor_path_lzw' => '/usr/bin/',
            ],
            // This mail configuration sends all emails to mailhog
            'MAIL' => [
                'transport' => 'smtp',
                'transport_smtp_server' => 'localhost:1025',
            ],
            'SYS' => [
                'trustedHostsPattern' => '.*.*',
                'devIPmask' => '*',
                'displayErrors' => 1,
                'encryptionKey' => 'e9471c9c39a5d84d446da687c520d6531949565ac6e383f42ca021bebf1960dd45ccad57b3143f12523941a0dcf3b0fe',
                'exceptionalErrors' => 20480,
                'isInitialDatabaseImportDone' => true,
                'isInitialInstallationInProgress' => false,
                'sitename' => 'Femanger Test Site',
                'sqlDebug' => 0,
                'systemLogLevel' => 2,
                't3lib_cs_convMethod' => 'mbstring',
                't3lib_cs_utils' => 'mbstring',
            ],
            'INSTALL' => [
                'wizardDone' => [
                    'TYPO3\CMS\Install\Updates\AccessRightParametersUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\BackendUserStartModuleUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\Compatibility6ExtractionUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\ContentTypesToTextMediaUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\FileListInAccessModuleListUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\FileListIsStartModuleUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\FilesReplacePermissionUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\LanguageIsoCodeUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\MediaceExtractionUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\MigrateMediaToAssetsForTextMediaCe' => 1,
                    'TYPO3\CMS\Install\Updates\MigrateShortcutUrlsAgainUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\OpenidExtractionUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\PageShortcutParentUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\ProcessedFileChecksumUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\SvgFilesSanitization' => 1,
                    'TYPO3\CMS\Install\Updates\TableFlexFormToTtContentFieldsUpdate' => 1,
                    'TYPO3\CMS\Install\Updates\WorkspacesNotificationSettingsUpdate' => 1,
                ],
            ],
            'EXT' => [
                'extConf' => [
                    'backend' => 'a:3:{s:9:"loginLogo";s:0:"";s:19:"loginHighlightColor";s:0:"";s:20:"loginBackgroundImage";s:0:"";}',
                    'belog' => 'a:0:{}',
                    'beuser' => 'a:0:{}',
                    'extensionmanager' => 'a:2:{s:21:"automaticInstallation";s:1:"1";s:11:"offlineMode";s:1:"0";}',
                    'feedit' => 'a:0:{}',
                    'felogin' => 'a:0:{}',
                    'femanager' => 'a:2:{s:13:"disableModule";s:1:"0";s:10:"disableLog";s:1:"0";}',
                    'fluid_styled_content' => 'a:1:{s:32:"loadContentElementWizardTsConfig";s:1:"1";}',
                    'func' => 'a:0:{}',
                    'info' => 'a:0:{}',
                    'lowlevel' => 'a:0:{}',
                    'rsaauth' => 'a:1:{s:18:"temporaryDirectory";s:0:"";}',
                    'saltedpasswords' => 'a:2:{s:3:"BE.";a:4:{s:21:"saltedPWHashingMethod";s:41:"TYPO3\\CMS\\Saltedpasswords\\Salt\\PhpassSalt";s:11:"forceSalted";i:0;s:15:"onlyAuthService";i:0;s:12:"updatePasswd";i:1;}s:3:"FE.";a:5:{s:7:"enabled";i:1;s:21:"saltedPWHashingMethod";s:41:"TYPO3\\CMS\\Saltedpasswords\\Salt\\PhpassSalt";s:11:"forceSalted";i:0;s:15:"onlyAuthService";i:0;s:12:"updatePasswd";i:1;}}',
                    'scheduler' => 'a:4:{s:11:"maxLifetime";s:4:"1440";s:11:"enableBELog";s:1:"1";s:15:"showSampleTasks";s:1:"1";s:11:"useAtdaemon";s:1:"0";}',
                    'setup' => 'a:0:{}',
                    'tstemplate' => 'a:0:{}',
                    'viewpage' => 'a:0:{}',
                ],
            ],
        ]
    );
}
