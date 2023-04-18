<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

$plugins = ['Registration', 'Edit', 'List', 'Detail', 'Invitation', 'ResendConfirmationMail'];

foreach ($plugins as $plugin) {
    $CType = 'femanager_' . strtolower($plugin);
    $flexformFile = 'FlexForm' . ucfirst($plugin);

    ExtensionUtility::registerPlugin(
        'femanager',
        $plugin,
        'LLL:EXT:femanager/Resources/Private/Language/locallang_mod.xlf:' . $CType . '.title',
        null,
        'femanager'
    );

    if ($plugin !== 'ResendConfirmationMail') {
        ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            'FILE:EXT:femanager/Configuration/FlexForms/' . $flexformFile . '.xml',
            $CType
        );

        $GLOBALS['TCA']['tt_content']['types'][$CType]['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,
            pi_flexform,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ';
    }
}

/**
 * Disable non needed fields in tt_content
 */
//$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['femanager_pi1'] = 'select_key'; //@todo
