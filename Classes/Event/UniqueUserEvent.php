<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class UniqueUserEvent
{
    public function __construct(private readonly string $emailOrUsername, private readonly string $fieldName, private readonly ?User $user, private bool $unique)
    {
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
