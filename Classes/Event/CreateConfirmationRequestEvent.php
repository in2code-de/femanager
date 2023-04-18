<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class CreateConfirmationRequestEvent extends UserEvent
{
    final public const MODE_AUTOMATIC = 'automatic';

    final public const MODE_MANUAL = 'manual';

    public function __construct(User $user, private readonly string $mode)
    {
        parent::__construct($user);
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
