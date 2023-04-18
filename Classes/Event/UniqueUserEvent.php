<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class UniqueUserEvent
{
    private readonly string $emailOrUsername;
    private readonly string $fieldName;
    private ?User $user = null;
    private bool $unique;

    public function __construct(
        string $emailOrUsername,
        string $fieldName,
        ?User $user,
        bool $unique
    ) {
        $this->emailOrUsername = $emailOrUsername;
        $this->fieldName = $fieldName;
        $this->user = $user;
        $this->unique = $unique;
    }

    public function setUnique(bool $unique): void
    {
        $this->unique = $unique;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getEmailOrUsername(): string
    {
        return $this->emailOrUsername;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
