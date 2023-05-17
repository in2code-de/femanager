<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class ImpersonateEvent
{
    public function __construct(protected ?User $user)
    {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
