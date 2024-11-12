<?php

namespace In2code\Femanager\Tests\Scripts;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SaveContent
 */
class SaveContent
{
    public function save(): string
    {
        $data = [
            'header' => 'New content from sendPost',
            'pid' => 29,
            'tstamp' => time(),
            'crdate' => time(),
            'CType' => 'text',
            'bodytext' => print_r($GLOBALS['TYPO3_REQUEST']->getParsedBody(), true) . '<p>[deleteme]</p>',
        ];
        /** @var $databaseconnection ConnectionPool */
        $databaseconnection = GeneralUtility::makeInstance(ConnectionPool::class);
        $databaseconnection->getConnectionForTable('tt_content')->insert('tt_content', $data);
        return 'New content element created on page 29';
    }
}
