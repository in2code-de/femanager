<?php

declare(strict_types = 1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Service\SendMailService;
use Symfony\Component\Mime\Email;

class BeforeMailSendEvent
{
    /**
     * @var Email
     */
    private $email;
    /**
     * @var array
     */
    private $variables;
    /**
     * @var SendMailService
     */
    private $service;

    public function __construct(Email $email, array $variables, SendMailService $service)
    {
        $this->email = $email;
        $this->variables = $variables;
        $this->service = $service;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return SendMailService
     */
    public function getService(): SendMailService
    {
        return $this->service;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param Email $email
     */
    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }
}
