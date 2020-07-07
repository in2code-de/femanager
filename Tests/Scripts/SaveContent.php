<?php

namespace In2code\Femanager\Tests\Scripts;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SaveContent
 */
class SaveContent
{

    /**
     * @return string
     */
    public function save()
    {
        $data = [
            'header' => 'New content from sendPost',
            'pid' => 29,
            'tstamp' => time(),
            'crdate' => time(),
            'CType' => 'text',
            'bodytext' => $this->getParams() . '<p>[deleteme]</p>'
        ];
        /** @var $databaseconnection ConnectionPool */
        $databaseconnection = GeneralUtility::makeInstance(ConnectionPool::class);
        $databaseconnection->getConnectionForTable('tt_content')->insert('tt_content', $data);
        return 'New content element created on page 29';
    }

    /**
     * Get GET/POST params
     */
    protected function getParams()
    {
        return print_r((array)$_POST + (array)$_GET, 1);
    }
}
