<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;
use Psr\Http\Message\RequestInterface;

class UserWasConfirmedByAdminEvent
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var User
     */
    protected $user;

    public function __construct(
        RequestInterface $request,
        User $user
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
