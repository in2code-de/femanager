<?php

declare(strict_types=1);

namespace In2code\Femanager\Domain\Service\Mail;

use TYPO3\CMS\Extbase\Mvc\RequestInterface;

interface MailServiceInterface
{
    public function send(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables = [],
        array $typoScript = [],
        RequestInterface|null $request = null
    ): bool;

    public function sendSimple(
        string $template,
        array $receiver,
        array $sender,
        string $subject,
        array $variables = [],
        array $typoScript = [],
        RequestInterface|null $request = null
    ): bool;
}
