<?php


namespace In2code\Femanager\EventListener;


use In2code\Femanager\Event\InviteUserCreateEvent;

class InviteUserSlotConsumer
{
    public function __invoke(InviteUserCreateEvent $event): void
    {
        $event->getRateLimiterService()->consumeSlot();
    }
}
