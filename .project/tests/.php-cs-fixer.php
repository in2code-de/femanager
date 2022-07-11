<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()->in(
    [
        __DIR__ . '/../../Classes',
        __DIR__ . '/../../Tests',
        __DIR__ . '/../../Configuration',
    ]
);
return $config;
