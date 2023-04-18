<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class InviteUserEditEvent extends UserEvent
{
    public function __construct(?User $user, private readonly string $hash)
    {
        parent::__construct($user);
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
