<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class BeforeUserConfirmEvent
{
    public function __construct(private readonly ?User $user, private readonly string $hash, private readonly string $status)
    {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
