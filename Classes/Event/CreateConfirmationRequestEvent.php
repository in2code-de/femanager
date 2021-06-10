<?php

declare(strict_types = 1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class CreateConfirmationRequestEvent extends UserEvent
{
    public const MODE_AUTOMATIC = 'automatic';

    public const MODE_MANUAL = 'manual';

    /**
     * @var string
     */
    private $mode;

    public function __construct(User $user, string $mode)
    {
        parent::__construct($user);
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}
