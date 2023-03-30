<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

/**
 * FE Plugin
 */
ExtensionUtility::registerPlugin('femanager', 'Pi1', 'FE_Manager');

$pluginSignatureRegistration = ExtensionUtility::registerPlugin(
    'femanager',
    'Registration',
    'Registration'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureRegistration] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignatureRegistration,
    'FILE:EXT:femanager/Configuration/FlexForms/FlexFormRegistration.xml'
);

/**
 * Flexform
 */
$pluginSignature = str_replace('_', '', 'femanager') . '_pi1';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:femanager/Configuration/FlexForms/FlexFormPi1.xml'
);

/**
 * Disable non needed fields in tt_content
 */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['femanager_pi1'] = 'select_key';
