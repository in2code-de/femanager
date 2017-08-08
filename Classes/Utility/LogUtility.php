<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Repository\LogRepository;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Class LogUtility
 */
class LogUtility extends AbstractUtility
{

    /**
     * @param int $state State to log
     * @param User $user Related User
     * @param array $additionalProperties for individual logging
     * @return void
     */
    public static function log($state, User $user, array $additionalProperties = [])
    {
        if (!ConfigurationUtility::isDisableLogActive()) {
            $log = self::getLog();
            $log->setTitle(LocalizationUtility::translateByState($state));
            $log->setState($state);
            $log->setUser($user);
            self::getLogRepository()->add($log);
        }
        self::getDispatcher()->dispatch(__CLASS__, __FUNCTION__ . 'Custom', [$state, $user, $additionalProperties]);
    }

    /**
     * @return Dispatcher
     */
    protected static function getDispatcher()
    {
        return self::getObjectManager()->get(Dispatcher::class);
    }

    /**
     * @return Log
     */
    protected static function getLog()
    {
        return self::getObjectManager()->get(Log::class);
    }

    /**
     * @return LogRepository
     */
    protected static function getLogRepository()
    {
        return self::getObjectManager()->get(LogRepository::class);
    }
}
