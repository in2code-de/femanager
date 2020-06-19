<?php
namespace In2code\Functions;

/**
 * Class ListUsers
 */
class ListUsers
{

    /**
     * @return string
     */
    public function listUsers()
    {
        $username = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('username');
        $content = '<h2>List FE_Users</h2>';
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        $databaseconnection = $GLOBALS['TYPO3_DB'];

        $select = 'fe_users.*';
        $from = 'fe_users';
        $where = 'fe_users.deleted = 0 and fe_users.pid = 5';
        if ($username !== null) {
            $where .= ' and fe_users.username = "' . $databaseconnection->quoteStr($username, $where) . '"';
        }
        $res = $databaseconnection->exec_SELECTquery($select, $from, $where);
        if ($res) {
            while (($row = $databaseconnection->sql_fetch_assoc($res))) {
                \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($row, 'in2code: ' . __CLASS__ . ':' . __LINE__);
            }
        }
        return $content;
    }
}
