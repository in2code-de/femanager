<?php
namespace In2code\Functions;

/**
 * Class UserLinks
 */
class UserLinks
{

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public $cObj;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->username = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('username');
        if ($this->username === null) {
            $this->username = $this->getLastUsername();
        }
        $this->pid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('pid');
        if ($this->pid === null) {
            throw new \Exception('pid missing like ?pid=123');
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function links()
    {
        $row = $this->getUserData($this->username);
        $content = '<h2>Get confirmation links of one FE_User</h2>';
        $content .= 'username: ' . $row['username'] . '<br />';
        $content .= 'email: ' . $row['email'] . '<br />';
        $content .= 'uid: ' . $row['uid'] . '<br />';
        $content .= 'status: ' . ($row['disable'] === '0' ? 'enabled' : 'disabled') . '<br />';
        $content .= '<a href="' . $this->getAdminConfirmationUri($row) . '">Admin confirmation link</a><br />';
        $content .= '<a href="' . $this->getUserConfirmationUri($row) . '">User confirmation link</a><br />';
        return $content;
    }

    /**
     * For
     * <f:uri.action action="confirmCreateRequest" controller="New" absolute="1"
     *      arguments="{user:user, hash:hash, status:'adminConfirmation'}" />
     *
     * @return string
     */
    public function getAdminConfirmationUri(array $row)
    {
        $params = '';
        $params .= '&tx_femanager_pi1[user]=' . $row['uid'];
        $params .= '&tx_femanager_pi1[hash]=' . $this->getHash($row);
        $params .= '&tx_femanager_pi1[status]=adminConfirmation';
        $params .= '&tx_femanager_pi1[action]=confirmCreateRequest';
        $params .= '&tx_femanager_pi1[controller]=New';
        $configuration = [
            'parameter' => (int) $this->pid,
            'additionalParams' => $params,
            'returnLast' => 'url',
            'useCacheHash' => '1'
        ];
        $uri = $this->cObj->typoLink_URL($configuration);
        return $uri;
    }

    /**
     * For
     * <f:uri.action action="confirmCreateRequest" controller="New" absolute="1"
     *      arguments="{user:user, hash:hash, status:'userConfirmation'}" />
     *
     * @return string
     */
    public function getUserConfirmationUri(array $row)
    {
        $params = '';
        $params .= '&tx_femanager_pi1[user]=' . $row['uid'];
        $params .= '&tx_femanager_pi1[hash]=' . $this->getHash($row);
        $params .= '&tx_femanager_pi1[status]=userConfirmation';
        $params .= '&tx_femanager_pi1[action]=confirmCreateRequest';
        $params .= '&tx_femanager_pi1[controller]=New';
        $configuration = [
            'parameter' => (int) $this->pid,
            'additionalParams' => $params,
            'returnLast' => 'url',
            'useCacheHash' => '1'
        ];
        $uri = $this->cObj->typoLink_URL($configuration);
        return $uri;
    }

    /**
     * @param array $row
     * @return string
     */
    protected function getHash(array $row)
    {
        $user = new \In2code\Femanager\Domain\Model\User();
        $user->setUsername($row['username']);
        return \In2code\Femanager\Utility\HashUtility::createHashForUser($user);
    }

    /**
     * @param $username
     * @return array|FALSE|NULL
     */
    protected function getUserData($username)
    {
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        $databaseconnection = $GLOBALS['TYPO3_DB'];
        $select = 'fe_users.*';
        $from = 'fe_users';
        $where = 'fe_users.deleted = 0 and fe_users.pid = 5' .
            ' and fe_users.username = "' . $databaseconnection->quoteStr($username, $from) . '"';
        return $databaseconnection->exec_SELECTgetSingleRow($select, $from, $where);
    }

    /**
     * @return array|FALSE|NULL
     */
    protected function getLastUsername()
    {
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        $databaseconnection = $GLOBALS['TYPO3_DB'];
        $select = 'fe_users.username';
        $from = 'fe_users';
        $where = 'fe_users.deleted = 0 and fe_users.pid = 5';
        $row = $databaseconnection->exec_SELECTgetSingleRow($select, $from, $where, '', 'uid desc');
        return $row['username'];
    }
}
