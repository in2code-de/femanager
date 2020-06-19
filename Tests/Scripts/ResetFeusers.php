<?php
namespace In2code\Functions;

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
    protected $userValues = array(
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
    );

    /**
     * @return string
     */
    public function reset()
    {
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        $databaseconnection = $GLOBALS['TYPO3_DB'];
        $databaseconnection->exec_DELETEquery('fe_users', 'username = "' . $this->userValues['username'] . '"');
        $databaseconnection->exec_INSERTquery('fe_users', $this->userValues);
        return 'FE Users reset successfully';
    }
}
