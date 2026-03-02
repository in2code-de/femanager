<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\LogRepository;
use In2code\Femanager\Event\UserLogEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class LogUtility
 */
class LogUtility
{
    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PersistenceManager $persistenceManager,
    ) {
    }

    /**
     * @param int $state State to log
     * @param ?User $user Related User
     * @param array $additionalProperties for individual logging
     * @codeCoverageIgnore
     */
    public function log($state, ?User $user = null, array $additionalProperties = []): void
    {
        if (!ConfigurationUtility::isDisableLogActive()) {
            $log = GeneralUtility::makeInstance(Log::class);
            $log->setTitle(LocalizationUtility::translateByState($state));
            $log->setState($state);

            if ($user) {
                $log->setUser($user);
            }

            if (!empty($additionalProperties)) {
                try {
                    $properties = json_encode(
                        $additionalProperties,
                        JSON_THROW_ON_ERROR |
                        JSON_INVALID_UTF8_SUBSTITUTE |
                        JSON_PARTIAL_OUTPUT_ON_ERROR
                    );
                } catch (\JsonException $exception) {
                    $properties = json_encode(['error' => 'Data not encodable']);
                }

                $log->setAdditionalProperties($properties);
            }
            $this->logRepository->add($log);
            // persist new log (in case an exception is thrown later the log is not persisted)
            $this->persistenceManager->persistAll();

        }

        $this->eventDispatcher->dispatch(new UserLogEvent($user, $state, $additionalProperties));
    }
}
