<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class InviteUserEditEvent extends UserEvent
{
    private readonly string $hash;

    public function __construct(?User $user, string $hash)
    {
        parent::__construct($user);

        $this->hash = $hash;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
