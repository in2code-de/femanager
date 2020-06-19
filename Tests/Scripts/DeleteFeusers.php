<?php
namespace In2code\Functions;

/**
 * Class DeleteFeusers
 */
class DeleteFeusers
{

    /**
     * @return string
     */
    public function delete()
    {
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        $databaseconnection = $GLOBALS['TYPO3_DB'];
        $databaseconnection->exec_updateQuery(
            'fe_users',
            'email not like "%@in2code.de"',
            array('deleted' => 1)
        );
        return 'All content elements deleted that have no in2code.de email address';
    }
}
