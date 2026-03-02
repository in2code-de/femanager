<?php

declare(strict_types=1);

namespace In2code\Femanager\Command;

use DateTimeImmutable;
use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

#[AsCommand(
    name: 'femanager:cleanup-logs',
    description: 'Removes femanager log entries that are soft-deleted, older than the given days or fe_user no longer exists.',
)]
class CleanupLogsCommand extends Command
{
    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Removes femanager log entries that are soft-deleted, older than the given days or fe_user no longer exists.');
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Simulate deletion without actually removing any records'
        );
        $this->addOption(
            'older-than-days',
            null,
            InputOption::VALUE_REQUIRED,
            'Also delete log entries older than the given number of days (e.g. 90)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = (bool)$input->getOption('dry-run');
        $olderThanDays = $input->getOption('older-than-days') !== null
            ? (int)$input->getOption('older-than-days')
            : null;

        if ($olderThanDays !== null && $olderThanDays <= 0) {
            $io->error('--older-than-days must be a positive integer.');
            return Command::FAILURE;
        }

        if ($isDryRun) {
            $io->note('Dry-run mode active – no records will be deleted.');
        }

        $deletedFlagCount = $this->deleteLogsSoftDeleted($isDryRun);
        $orphanedCount = $this->deleteLogsWithMissingUser($isDryRun);

        $tableRows = [
            ['Log entries with deleted=1', $deletedFlagCount],
            ['Log entries with non-existing fe_user', $orphanedCount],
        ];

        if ($olderThanDays !== null) {
            $cutoffDate = (new DateTimeImmutable())->modify('-' . $olderThanDays . ' days');
            $outdatedCount = $this->deleteLogsOlderThan($cutoffDate, $isDryRun);
            $tableRows[] = ['Log entries older than ' . $olderThanDays . ' days', $outdatedCount];
        } else {
            $outdatedCount = 0;
        }

        $tableRows[] = ['Total', $deletedFlagCount + $orphanedCount + $outdatedCount];

        $io->table(['Reason', 'Records removed'], $tableRows);

        $io->success('Cleanup finished.');

        return Command::SUCCESS;
    }

    private function deleteLogsSoftDeleted(bool $isDryRun): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(Log::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $count = (int)$queryBuilder
            ->count('uid')
            ->from(Log::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchOne();

        if ($count > 0 && $isDryRun === false) {
            $deleteQueryBuilder = $this->connectionPool->getQueryBuilderForTable(Log::TABLE_NAME);
            $deleteQueryBuilder->getRestrictions()->removeAll();
            $deleteQueryBuilder
                ->delete(Log::TABLE_NAME)
                ->where($deleteQueryBuilder->expr()->eq('deleted', $deleteQueryBuilder->createNamedParameter(1, Connection::PARAM_INT)))
                ->executeStatement();
        }

        return $count;
    }

    private function deleteLogsWithMissingUser(bool $isDryRun): int
    {
        $orphanedUids = $this->findOrphanedLogUids();

        if ($orphanedUids === []) {
            return 0;
        }

        if ($isDryRun === false) {
            $deleteQueryBuilder = $this->connectionPool->getQueryBuilderForTable(Log::TABLE_NAME);
            $deleteQueryBuilder->getRestrictions()->removeAll();
            $deleteQueryBuilder
                ->delete(Log::TABLE_NAME)
                ->where(
                    $deleteQueryBuilder->expr()->in(
                        'uid',
                        $deleteQueryBuilder->createNamedParameter($orphanedUids, Connection::PARAM_INT_ARRAY)
                    )
                )
                ->executeStatement();
        }

        return count($orphanedUids);
    }

    private function findOrphanedLogUids(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(Log::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('log.uid')
            ->from(Log::TABLE_NAME, 'log')
            ->leftJoin(
                'log',
                User::TABLE_NAME,
                'u',
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('log.user', $queryBuilder->quoteIdentifier('u.uid')),
                    $queryBuilder->expr()->eq('u.deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
                )
            )
            ->where(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->gt('log.user', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                    $queryBuilder->expr()->isNull('u.uid')
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_column($rows, 'uid');
    }

    private function deleteLogsOlderThan(DateTimeImmutable $cutoffDate, bool $isDryRun): int
    {
        $cutoffTimestamp = $cutoffDate->getTimestamp();

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(Log::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $count = (int)$queryBuilder
            ->count('uid')
            ->from(Log::TABLE_NAME)
            ->where($queryBuilder->expr()->lt('tstamp', $queryBuilder->createNamedParameter($cutoffTimestamp, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchOne();

        if ($count > 0 && $isDryRun === false) {
            $deleteQueryBuilder = $this->connectionPool->getQueryBuilderForTable(Log::TABLE_NAME);
            $deleteQueryBuilder->getRestrictions()->removeAll();
            $deleteQueryBuilder
                ->delete(Log::TABLE_NAME)
                ->where($deleteQueryBuilder->expr()->lt('tstamp', $deleteQueryBuilder->createNamedParameter($cutoffTimestamp, Connection::PARAM_INT)))
                ->executeStatement();
        }

        return $count;
    }
}
