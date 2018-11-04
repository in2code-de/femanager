<?php
declare(strict_types=1);

namespace In2code\Femanager\Command;

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use In2code\Femanager\Utility\ObjectUtility;


/**
 * Class TaskCommandController
 */
class TaskCommandController extends CommandController
{

    /**
     * current timestamp for comparison
     *
     * @var int $currentTime
     */

    protected $currentTime;


    /**
     * TaskCommandController constructor.
     *
     * @param integer $now
     *
     */

    public function __construct()
    {
        $this->currentTime = time();
    }

    /**
     * Femanager: Remove unconfirmed fe_user records from database
     *
     *  This task can clean up users that did not confirm their registration
     *
     * @param integer $period Define a number of days. All unconfirmed users that signed more days ago than the chosen days will be deleted (Default is 1 day)
     *
     * @return void
     */

    public function cleanUsersThatDidNotConfirmCommand(int $period = 1)
    {
        $queryBuilder = ObjectUtility::getQueryBuilder('fe_users');
        $queryBuilder
            ->delete('fe_users')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('disable', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->gt('tx_femanager_unconfirmed_since', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->lt('tx_femanager_unconfirmed_since', $queryBuilder->createNamedParameter($this->getCompareTime((int)$period), \PDO::PARAM_INT))
                )
            )
            ->execute();
    }

    /**
     *
     *
     *
     * @param integer $period
     * @return integer
     */

    protected function getCompareTime(int $period)
    {
        return (int)$this->currentTime - ((int)$period * 86400);
    }
}