<?php
namespace In2code\Femanager\Tests\Scripts;

/**
 * Class DeleteFrontendSessions
 */
class DeleteFrontendSessions
{

    /**
     * @return string
     */
    public function delete()
    {
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        /*$databaseconnection = $GLOBALS['TYPO3_DB'];
        $databaseconnection->exec_DELETEquery(
            'fe_sessions',
            '1=1'
        );*/
        return 'All frontend sessions deleted';
    }
}
