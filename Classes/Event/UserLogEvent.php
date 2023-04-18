<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class UserLogEvent extends UserEvent
{
    private readonly int $state;
    private readonly array $additionalProperties;

    public function __construct(?User $user, int $state, array $additionalProperties = [])
    {
        parent::__construct($user);

        $this->state = $state;
        $this->additionalProperties = $additionalProperties;
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
