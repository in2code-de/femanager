<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "femanager"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'femanager',
    'description' => 'TYPO3 Frontend User Registration and Management based on
        Extbase and Fluid and on TYPO3 7.6 and the possibility to extend it.
        Extension basicly works like sr_feuser_register',
    'category' => 'plugin',
    'author' => 'femanager dev team',
    'author_email' => 'info@in2code.de',
    'author_company' => 'in2code.de - Wir leben TYPO3',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '2.0.0',
    'constraints' => array(
        'depends' => array(
            'extbase' => '7.6.0-7.99.99',
            'fluid' => '7.6.0-7.99.99',
            'typo3' => '7.6.0-7.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(
            'sr_freecap' => '2.0.4-2.99.99',
            'static_info_tables' => '6.0.0-6.99.99'
        ),
    ),
);
