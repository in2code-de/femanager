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
    /**
     * @return string
     */
    public function delete()
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
            $queryBuilder->execute();

            return 'All content elements deleted that have no in2code.de email address';
        } catch (DBALException $e) {
            $errorMsg = $e->getMessage();
        }
        return 'Could not delete fe_users. ' . $errorMsg;
    }
}
