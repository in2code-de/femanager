<?php
declare(strict_types = 1);
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\LogRepository;
use In2code\Femanager\Event\UserLogEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LogUtility
 */
class LogUtility
{
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(LogRepository $logRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->logRepository = $logRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $state State to log
     * @param User $user Related User
     * @param array $additionalProperties for individual logging
     * @codeCoverageIgnore
     */
    public function log($state, User $user, array $additionalProperties = [])
    {
        if (!ConfigurationUtility::isDisableLogActive()) {
            $log = GeneralUtility::makeInstance(Log::class);
            $log->setTitle(LocalizationUtility::translateByState($state));
            $log->setState($state);
            $log->setUser($user);
            $this->logRepository->add($log);
        }

        $this->eventDispatcher->dispatch(new UserLogEvent($user, $state, $additionalProperties));
    }
}
