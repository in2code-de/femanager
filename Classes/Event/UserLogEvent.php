<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class UserLogEvent
{
    public function __construct(
        protected ?User $user,
        private readonly int $state,
        private readonly array $additionalProperties = []
    ) {}

    public function getAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
