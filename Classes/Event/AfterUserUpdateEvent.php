<?php

declare(strict_types = 1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class AfterUserUpdateEvent extends UserEvent
{
    /**
     * @var string
     */
    private $hash;
    /**
     * @var string
     */
    private $status;

    public function __construct(User $user, string $hash, string $status)
    {
        parent::__construct($user);
        $this->hash = $hash;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }
}
