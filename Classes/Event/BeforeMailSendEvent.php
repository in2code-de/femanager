<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Service\SendMailService;
use Symfony\Component\Mime\Email;

class BeforeMailSendEvent
{
    public function __construct(private Email $email, private readonly array $variables, private readonly SendMailService $service)
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

    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }
}
