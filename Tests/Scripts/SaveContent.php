<?php
namespace In2code\Functions;

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
        /** @var $databaseconnection \TYPO3\CMS\Core\Database\DatabaseConnection */
        $databaseconnection = $GLOBALS['TYPO3_DB'];
        $databaseconnection->exec_insertQuery(
            'tt_content',
            array(
                'header' => 'New content from sendPost',
                'pid' => 29,
                'tstamp' => time(),
                'crdate' => time(),
                'CType' => 'text',
                'bodytext' => $this->getParams() . '<p>[deleteme]</p>'
            )
        );
        return 'New content element created on page 29';
    }

    /**
     * Get GET/POST params
     */
    protected function getParams()
    {
        return print_r((array) $_POST + (array) $_GET, 1);
    }
}
