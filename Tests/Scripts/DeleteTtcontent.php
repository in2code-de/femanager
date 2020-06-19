<?php
namespace In2code\Functions;

/**
 * Class DeleteTtcontent
 */
class DeleteTtcontent
{

    /**
     * @return string
     */
    public function delete()
    {
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        $databaseconnection = $GLOBALS['TYPO3_DB'];
        $databaseconnection->exec_updateQuery(
            'tt_content',
            'bodytext like "%[deleteme]%"',
            array('deleted' => 1)
        );
        return 'All content elements deleted with query: bodytext like "%[deleteme]%"';
    }
}
