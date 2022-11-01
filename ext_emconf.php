<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "femanager"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'femanager',
    'description' => 'TYPO3 Frontend User Registration and Management based on
        Extbase and Fluid and on TYPO3 11 and the possibility to extend it.
        Extension basicly works like sr_feuser_register',
    'category' => 'plugin',
    'author' => 'Alexander Kellner, Stefan Busemann',
    'author_email' => 'info@in2code.de',
    'author_company' => 'in2code.de - Wir leben TYPO3',
    'state' => 'stable',
    'version' => '7.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'php' => '7.4.0-7.4.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'sr_freecap' => '2.3.0-2.99.99',
            'static_info_tables' => '6.9.0-6.99.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'In2code\\Femanager\\' => 'Classes'
        ]
    ],
];
