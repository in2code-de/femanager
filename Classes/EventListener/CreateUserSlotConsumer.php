<?php


namespace In2code\Femanager\EventListener;


use In2code\Femanager\Event\BeforeUserCreateEvent;

class CreateUserSlotConsumer
{
    public function __invoke(BeforeUserCreateEvent $event): void
    {
        $event->getRateLimiterService()->consumeSlot();
    }
}
