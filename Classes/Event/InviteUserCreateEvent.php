<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\RatelimiterService;

class InviteUserCreateEvent extends UserEvent
{
    protected $rateLimiterService;

    public function __construct(?User $user, RatelimiterService $rateLimiterService)
    {
        parent::__construct($user);
        $this->rateLimiterService = $rateLimiterService;
    }

    /**
     * @return RatelimiterService
     */
    public function getRateLimiterService(): RatelimiterService
    {
        return $this->rateLimiterService;
    }

}
