<?php
declare(strict_types = 1);
namespace In2code\Femanager\Domain\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StoreInDatabaseService
 */
class StoreInDatabaseService
{

    /**
     * Database Table to store
     *
     * @var string
     */
    protected $table = '';

    /**
     * Array with fieldname=>value
     *
     * @var array
     */
    protected $properties = [];

    /**
     * @var \TYPO3\CMS\CORE\Database\ConnectionPool
     */
    protected $databaseConnection = null;

    /**
     * Executes the storage
     *
     * @return int uid of inserted record
     */
    public function execute()
    {
        $insertTable = $this->databaseConnection->getConnectionForTable($this->getTable());
        $insertTable->insert(
            $this->getTable(),
            $this->getProperties()
        );

        return (int)$insertTable->lastInsertId($this->getTable());
    }

    /**
     * Set TableName
     *
     * @param string $table
     */
    public function setTable($table)
    {
        $table = preg_replace('/[^a-zA-Z0-9_-]/', '', $table);
        $this->table = $table;
    }

    /**
     * Get TableName
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Read properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Add property/value pair to array
     *
     * @param $propertyName
     * @param $value
     */
    public function addProperty($propertyName, $value)
    {
        $propertyName = preg_replace('/[^a-zA-Z0-9_-]/', '', $propertyName);
        $this->properties[$propertyName] = $value;
    }

    /**
     * Remove property/value pair form array by its key
     *
     * @param $propertyName
     */
    public function removeProperty($propertyName)
    {
        unset($this->properties[$propertyName]);
    }

    /**
     * Initialize
     */
    public function __construct()
    {
        $this->databaseConnection = GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
