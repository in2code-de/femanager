<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;

class BeforeUserConfirmEvent
{
    private ?User $user = null;
    private readonly string $hash;
    private readonly string $status;

    public function __construct(
        ?User $user,
        string $hash,
        string $status
    ) {
        $this->user = $user;
        $this->hash = $hash;
        $this->status = $status;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
