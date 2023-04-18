<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Service\SendMailService;
use Symfony\Component\Mime\Email;

class AfterMailSendEvent
{
    public function __construct(private readonly Email $email, private readonly array $variables, private readonly SendMailService $service)
    {
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getService(): SendMailService
    {
        return $this->service;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
