<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class AfterUserUpdateEvent
{
    public function __construct(protected User $user, private readonly string $hash, private readonly string $status)
    {
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
