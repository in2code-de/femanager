<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class FinalCreateEvent extends UserEvent
{
    public function __construct(?User $user, private readonly string $action)
    {
        parent::__construct($user);
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
