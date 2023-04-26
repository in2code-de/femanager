<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class UserLogEvent extends UserEvent
{
    public function __construct(?User $user, private readonly int $state, private readonly array $additionalProperties = [])
    {
        parent::__construct($user);
    }

    public function getAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    public function getState(): int
    {
        return $this->state;
    }
}
