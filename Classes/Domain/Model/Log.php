<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Model;

use DateTimeImmutable;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Log
 */
class Log extends AbstractEntity
{
    public const TABLE_NAME = 'tx_femanager_domain_model_log';

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

    final public const STATUS_PROFILEUPDATEATTEMPTEDSPOOF = 207;

    final public const STATUS_PROFILEUPDATENOTAUTHORIZED = 208;

    final public const STATUS_PROFILEDELETE = 301;

    final public const STATUS_INVITATIONPROFILECREATED = 401;

    final public const STATUS_INVITATIONPROFILEDELETEDUSER = 402;

    final public const STATUS_INVITATIONHASHERROR = 403;

    final public const STATUS_INVITATIONRESTRICTEDPAGE = 404;

    final public const STATUS_INVITATIONPROFILEENABLED = 405;

    final public const STATUS_LOGIN_AS = 501;
    final public const STATUS_LOGIN_AS_DENIED = 502;

    final public const STATUS_FRONTEND_LOGIN_SUCCESSFUL = 601;
    final public const STATUS_FRONTEND_LOGIN_FAILED = 602;

    protected string $title;
    protected int $state;
    protected ?User $user = null;
    protected DateTimeImmutable $tstamp;
    protected string $additionalProperties = '';

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setState(int $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getAdditionalProperties(): string
    {
        return $this->additionalProperties;
    }

    public function setAdditionalProperties(string $additionalProperties): void
    {
        $this->additionalProperties = $additionalProperties;
    }

    public function getAdditionalPropertiesAsArray(): array
    {
        return json_decode($this->additionalProperties, true) ?? [];
    }

    public function getTstamp(): DateTimeImmutable
    {
        return $this->tstamp;
    }

    public function setTstamp(DateTimeImmutable $tstamp): void
    {
        $this->tstamp = $tstamp;
    }
}
