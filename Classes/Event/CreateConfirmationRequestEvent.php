<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class CreateConfirmationRequestEvent extends UserEvent
{
    final public const MODE_AUTOMATIC = 'automatic';

    final public const MODE_MANUAL = 'manual';

    private readonly string $mode;

    public function __construct(User $user, string $mode)
    {
        parent::__construct($user);
        $this->mode = $mode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
