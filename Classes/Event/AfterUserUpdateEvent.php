<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class AfterUserUpdateEvent extends UserEvent
{
    public function __construct(User $user, private readonly string $hash, private readonly string $status)
    {
        parent::__construct($user);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
