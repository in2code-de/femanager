<?php
declare(strict_types=1);
namespace In2code\Femanager\Tests\Unit\Fixture\Utility;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Repository\LogRepository;
use In2code\Femanager\Utility\LogUtility as LogUtilityFemanager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Class LogUtility
 */
class LogUtility extends LogUtilityFemanager
{

    /**
     * @return Dispatcher
     */
    public static function getDispatcherPublic()
    {
        return self::getDispatcher();
    }

    /**
     * @return Log
     */
    public static function getLogPublic()
    {
        return self::getLog();
    }

    /**
     * @return LogRepository
     */
    public static function getLogRepositoryPublic()
    {
        return self::getLogRepository();
    }
}
