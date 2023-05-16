<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class CreateConfirmationRequestEvent
{
    final public const MODE_AUTOMATIC = 'automatic';

    final public const MODE_MANUAL = 'manual';

    public function __construct(protected User $user, private readonly string $mode)
    {
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
