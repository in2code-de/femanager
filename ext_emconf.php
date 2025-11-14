<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "femanager"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'femanager',
    'description' => 'TYPO3 Frontend User Registration and Management based on
        Extbase and Fluid and on TYPO3 12 and the possibility to extend it.',
    'category' => 'plugin',
    'author' => 'Alexander Kellner, Stefan Busemann, Daniel Hoffmann, Sebastian Stein',
    'author_email' => 'info@in2code.de',
    'author_company' => 'in2code.de - Wir leben TYPO3',
    'state' => 'stable',
    'version' => '8.3.3',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.4.99',
            'php' => '8.1.0-',
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
