<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class ImpersonateEvent
{
    public function __construct(protected ?User $user, protected ?int $backendUserId)
    {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getBackendUserId(): ?int
    {
        return $this->backendUserId;
    }
}
