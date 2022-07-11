<?php

namespace In2code\Femanager\Tests\Scripts;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ResetFeusers
 */
class ResetFeusers
{
    /**
     * Values to insert
     *
     * @var array
     */
    protected $userValues = [
        'pid' => 5,
        'tstamp' => 1448210411,
        'crdate' => 1448210411,
        'username' => 'akellner',
        'password' => '$P$CZ77atH8UNCFBeFfxsHB9V9CDR0.CN.',
        'usergroup' => '1',
        'name' => '',
        'first_name' => 'Alex',
        'last_name' => 'Kellner',
        'email' => 'alex@in2code.de',
        'tx_femanager_log' => '1'
    ];

    /**
     * @return string
     */
    public function reset()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder2 = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder
            ->delete('fe_users')
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($this->userValues['username']))
            );
        $queryBuilder2->insert('fe_users')->values($this->userValues);

        try {
            $queryBuilder->execute();
            $queryBuilder2->execute();

            return 'FE Users reset successfully';
        } catch (DBALException $e) {
            $errorMsg = $e->getMessage();
        }

        return 'Could not delete fe_users. ' . $errorMsg;
    }
}
