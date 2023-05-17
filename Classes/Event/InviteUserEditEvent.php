<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class InviteUserEditEvent
{
    public function __construct(protected ?User $user, private readonly string $hash)
    {
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
