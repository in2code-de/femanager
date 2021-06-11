<?php

declare(strict_types = 1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class BeforeUserConfirmEvent
{
    /**
     * @var User|null
     */
    private $user;
    /**
     * @var string
     */
    private $hash;
    /**
     * @var string
     */
    private $status;

    public function __construct(
        ?User $user,
        string $hash,
        string $status
    ) {
        $this->user = $user;
        $this->hash = $hash;
        $this->status = $status;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
