<?php
declare(strict_types=1);
namespace In2code\Femanager\Domain\Service;

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
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection = null;

    /**
     * Executes the storage
     *
     * @return int uid of inserted record
     */
    public function execute()
    {
        $this->databaseConnection->exec_INSERTquery($this->getTable(), $this->getProperties());
        return $this->databaseConnection->sql_insert_id();
    }

    /**
     * Set TableName
     *
     * @param string $table
     * @return void
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
     * @return void
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
     * @return void
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
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
    }
}
