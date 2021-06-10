<?php

declare(strict_types = 1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class FinalCreateEvent extends UserEvent
{
    /**
     * @var string
     */
    private $action;

    public function __construct(?User $user, string $action)
    {
        parent::__construct($user);

        $this->action = $action;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
