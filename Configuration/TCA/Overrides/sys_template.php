<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
defined('TYPO3') or die();

/**
 * Static TypoScript
 */
ExtensionManagementUtility::addStaticFile(
    'femanager',
    'Configuration/TypoScript/Main',
    'Main Settings'
);
ExtensionManagementUtility::addStaticFile(
    'femanager',
    'Configuration/TypoScript/Layout',
    'Add Layout CSS'
);
