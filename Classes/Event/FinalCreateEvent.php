<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class FinalCreateEvent
{
    public function __construct(protected ?User $user, private readonly string $action) {}

    public function getAction(): string
    {
        return $this->action;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
