<?php

declare(strict_types=1);

namespace In2code\Femanager\EventListener;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Utility\LogUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\FrontendLogin\Event\LoginConfirmedEvent;
use TYPO3\CMS\FrontendLogin\Event\LoginErrorOccurredEvent;

#[AsEventListener(
    identifier: 'femanager/frontend-login',
)]
final class FrontendLoginEventListener
{
    public function __construct(private readonly LogUtility $logUtility)
    {
    }

    public function __invoke(LoginConfirmedEvent|LoginErrorOccurredEvent $event): void
    {
        $username = $event->getRequest()->getParsedBody()['user'] ?? '';
        switch ($event::class) {
            case LoginErrorOccurredEvent::class:
                $this->logUtility->log(LOG::STATUS_FRONTEND_LOGIN_FAILED, null, ['user' => $username]);
                break;
            case LoginConfirmedEvent::class:
                $this->logUtility->log(LOG::STATUS_FRONTEND_LOGIN_SUCCESSFUL, null, ['user' => $username]);
                break;
            default:
                break;
        }
    }
}
