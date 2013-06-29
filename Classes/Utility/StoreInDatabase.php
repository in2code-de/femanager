<?php
namespace In2\Femanager\Utility;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alex Kellner <alexander.kellner@in2code.de>, in2code
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Store values in any table
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StoreInDatabase {

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
	protected $properties = array();

	/**
	 * Executes the storage
	 */
	public function execute() {
		\TYPO3\CMS\Core\Utility\DebugUtility::debug($this->table, 'in2code Debug: ' . __FILE__ . ' in Line: ' . __LINE__);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->getTable(), $values);
	}

	/**
	 * Set TableName
	 *
	 * @param string $table
	 * @return void
	 */
	public function setTable($table) {
		$this->table = str_replace('.', '', $table);
	}

	/**
	 * Get TableName
	 *
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Add property/value pair to array
	 *
	 * @param $propertyName
	 * @param $value
	 * @return void
	 */
	public function addProperty($propertyName, $value) {
		$this->properties[$propertyName] = $value;
	}

	/**
	 * Remove property/value pair form array by its key
	 *
	 * @param $propertyName
	 * @return void
	 */
	public function removeProperty($propertyName) {
		unset($this->properties[$propertyName]);
	}
	
}

?>