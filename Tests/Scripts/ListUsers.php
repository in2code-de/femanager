<?php

namespace In2code\Femanager\Tests\Scripts;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ListUsers
 */

class ListUsers
{
    /**
     *  This is a test function for a behat test
     *  in web/typo3conf/ext/femanager/Tests/Behaviour/Features/Edit/Default/SmallNoConfirm.feature
     *  Scenario: Login as frontend user and test profile update
     *  Given I am on "/index.php?id=33" ==> list random values
     *
     * @return string
     */
    public function listUsers()
    {
        $content = '<h2>List FE_Users</h2>';

        $username = GeneralUtility::_GET('username');
        if (!$username) {
            $username = 'akellner';
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder->select('*')->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username))
            );
        try {
            $res = $queryBuilder->executeQuery();
            if ($res) {
                while (($row = $res->fetchAssociative())) {
                    $content .= DebuggerUtility::var_dump(
                        $row,
                        'in2code: ' . self::class . ':' . __LINE__,
                        8,
                        false,
                        true,
                        true
                    );
                }
            }
        } catch (DBALException $e) {
            $content = 'error: ' . $e->getMessage();
        }

        return $content;
    }

    /**
     *  This is a test function for a behat test
     *  in web/typo3conf/ext/femanager/Tests/Behaviour/Features/Edit/Default/SmallNoConfirm.feature
     *  Scenario: Login as frontend user and test profile update
     *  Given I am on "/index.php?id=33" ==> list random values
     *
     * @return string
     */
    public function listLastestUser()
    {
        $content = '<h2>List latest FE_Users</h2>';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder->select('*')->from('fe_users')->orderBy('uid', 'desc')->setMaxResults(1);

        try {
            $res = $queryBuilder->executeQuery();
            if ($res) {
                $row = $res->fetchAssociative();
                $content .= DebuggerUtility::var_dump(
                    $row,
                    'in2code: ' . self::class . ':' . __LINE__,
                    8,
                    false,
                    true,
                    true
                );
            }
        } catch (DBALException $e) {
            $content = 'error: ' . $e->getMessage();
        }

        return $content;
    }
}
