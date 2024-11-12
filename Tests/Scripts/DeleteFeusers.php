<?php

namespace In2code\Femanager\Tests\Scripts;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DeleteFeusers
 */
class DeleteFeusers
{
    public function test(): void
    {
        echo 'test';
    }

    public function delete(): string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder
            ->delete('fe_users')
            ->where(
                $queryBuilder->expr()->notLike(
                    'email',
                    $queryBuilder->createNamedParameter(
                        '%' . $queryBuilder->escapeLikeWildcards('@in2code.de') . '%'
                    )
                )
            );

        try {
            $queryBuilder->executeStatement();

            return 'All content elements deleted that have no in2code.de email address';
        } catch (DBALException $dbalException) {
            $errorMsg = $dbalException->getMessage();
        }

        return 'Could not delete fe_users. ' . $errorMsg;
    }
}
