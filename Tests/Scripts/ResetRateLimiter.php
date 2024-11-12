<?php

namespace In2code\Femanager\Tests\Scripts;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DeleteFeusers
 */
class ResetRateLimiter
{
    public function reset(): string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('cache_femanager_ratelimiter');
        $queryBuilder
            ->delete('cache_femanager_ratelimiter');

        try {
            $queryBuilder->executeStatement();

            return 'Rate limiter cache has been reset';
        } catch (Exception $exception) {
            $errorMsg = $exception->getMessage();
        }

        return 'Could not reset rate limiter. ' . $errorMsg;
    }
}
