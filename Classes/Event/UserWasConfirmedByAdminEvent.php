<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;
use Psr\Http\Message\RequestInterface;

class UserWasConfirmedByAdminEvent
{
    public function __construct(
        protected RequestInterface $request,
        protected User $user
    ) {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
