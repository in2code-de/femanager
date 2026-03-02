<?php

declare(strict_types=1);

defined('TYPO3') || die();

return [
    'femanager:cleanup-logs' => [
        'class' => \In2code\Femanager\Command\CleanupLogsCommand::class,
        'schedulable' => true,
    ],
];