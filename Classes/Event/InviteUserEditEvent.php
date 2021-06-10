<?php

declare(strict_types = 1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class InviteUserEditEvent extends UserEvent
{
    /**
     * @var string
     */
    private $hash;

    public function __construct(?User $user, string $hash)
    {
        parent::__construct($user);

        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }
}
