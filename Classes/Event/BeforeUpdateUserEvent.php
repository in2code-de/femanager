<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class BeforeUpdateUserEvent
{
    public function __construct(protected ?\In2code\Femanager\Domain\Model\User $user)
    {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
