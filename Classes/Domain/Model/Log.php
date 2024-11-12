<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Log
 */
class Log extends AbstractEntity
{
    final public const STATUS_NEWREGISTRATION = 101;

    final public const STATUS_REGISTRATIONCONFIRMEDUSER = 102;

    final public const STATUS_REGISTRATIONCONFIRMEDADMIN = 103;

    final public const STATUS_REGISTRATIONREFUSEDUSER = 104;

    final public const STATUS_REGISTRATIONREFUSEDADMIN = 105;

    final public const STATUS_PROFILECREATIONREQUEST = 106;

    final public const STATUS_PROFILEUPDATED = 201;

    final public const STATUS_PROFILEUPDATECONFIRMEDADMIN = 202;

    final public const STATUS_PROFILEUPDATEREFUSEDADMIN = 203;

    final public const STATUS_PROFILEUPDATEREQUEST = 204;

    final public const STATUS_PROFILEUPDATEREFUSEDSECURITY = 205;

    final public const STATUS_PROFILEUPDATEIMAGEDELETE = 206;

    final public const STATUS_PROFILEDELETE = 301;

    final public const STATUS_INVITATIONPROFILECREATED = 401;

    final public const STATUS_INVITATIONPROFILEDELETEDUSER = 402;

    final public const STATUS_INVITATIONHASHERROR = 403;

    final public const STATUS_INVITATIONRESTRICTEDPAGE = 404;

    final public const STATUS_INVITATIONPROFILEENABLED = 405;

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
     */
    public function setTitle($title): static
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
     */
    public function setState($state): static
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
     */
    public function setUser(User $user): static
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
