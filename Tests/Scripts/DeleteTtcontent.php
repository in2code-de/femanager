<?php

namespace In2code\Femanager\Tests\Scripts;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $query = $connectionPool->getQueryBuilderForTable('tt_content');
        $query->update('tt_content')
            ->set('deleted', 1)
            ->where($query->expr()->like('bodytext', $query->createNamedParameter('%[deleteme]%')));
        $rowCount = $query->execute();
        return 'All content elements deleted with query: bodytext like "%[deleteme]%" (' . $rowCount . ' rows)';
    }
}
