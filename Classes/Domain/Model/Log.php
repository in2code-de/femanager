<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Log
 */
class Log extends AbstractEntity
{
    const STATUS_NEWREGISTRATION = 101;
    const STATUS_REGISTRATIONCONFIRMEDUSER = 102;
    const STATUS_REGISTRATIONCONFIRMEDADMIN = 103;
    const STATUS_REGISTRATIONREFUSEDUSER = 104;
    const STATUS_REGISTRATIONREFUSEDADMIN = 105;
    const STATUS_PROFILECREATIONREQUEST = 106;
    const STATUS_PROFILEUPDATED = 201;
    const STATUS_PROFILEUPDATECONFIRMEDADMIN = 202;
    const STATUS_PROFILEUPDATEREFUSEDADMIN = 203;
    const STATUS_PROFILEUPDATEREQUEST = 204;
    const STATUS_PROFILEUPDATEREFUSEDSECURITY = 205;
    const STATUS_PROFILEUPDATEIMAGEDELETE = 206;
    const STATUS_PROFILEDELETE = 301;
    const STATUS_INVITATIONPROFILECREATED = 401;
    const STATUS_INVITATIONPROFILEDELETEDUSER = 402;
    const STATUS_INVITATIONHASHERROR = 403;
    const STATUS_INVITATIONRESTRICTEDPAGE = 404;
    const STATUS_INVITATIONPROFILEENABLED = 405;

    /**
     * title
     *
     * @var string
     */
    protected $title;

    /**
     * state
     *
     * @var int
     */
    protected $state;

    /**
     * user
     *
     * @var User
     */
    protected $user;

    /**
     * @param string $title
     * @return Log
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $state
     * @return Log
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Log
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
