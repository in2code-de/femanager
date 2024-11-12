<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "femanager"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'femanager',
    'description' => 'TYPO3 Frontend User Registration and Management based on
        Extbase and Fluid and on TYPO3 12 and the possibility to extend it.',
    'category' => 'plugin',
    'author' => 'Alexander Kellner, Stefan Busemann, Daniel Hoffmann',
    'author_email' => 'info@in2code.de',
    'author_company' => 'in2code.de - Wir leben TYPO3',
    'state' => 'stable',
    'version' => '8.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.4.99',
            'php' => '8.1.0-',
        ],
        'conflicts' => [],
        'suggests' => [
            'sr_freecap' => '13.4.0-13.4.99',
            'static_info_tables' => '13.4.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'In2code\\Femanager\\' => 'Classes'
        ]
    ],
];
