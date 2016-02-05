<?php
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Service\StoreInDatabaseService;
use In2code\Femanager\Utility\StringUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
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
 * Save values to any table
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SaveToAnyTableFinisher extends AbstractFinisher implements FinisherInterface
{

    /**
     * Inject a complete new content object
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     * @inject
     */
    protected $contentObject;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
     * @inject
     */
    protected $typoScriptService;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var int
     */
    protected $lastGeneratedUid = 0;

    /**
     * @var array
     */
    protected $dataArray = [];

    /**
     * Overwrite configuration with
     *      plugin.tx_femanager.settings.new.storeInDatabase
     *
     * @return void
     */
    public function initializeFinisher()
    {
        $configuration = $this->typoScriptService->convertPlainArrayToTypoScriptArray($this->settings);
        if (!empty($configuration['new.']['storeInDatabase.'])) {
            $this->configuration = $configuration['new.']['storeInDatabase.'];
        }
    }

    /**
     * Store to any table
     *
     * @return void
     */
    public function storeFinisher()
    {
        if (!empty($this->configuration)) {
            $this->addArrayToDataArray($this->user->_getProperties());
            foreach ((array) array_keys($this->configuration) as $table) {
                $this->storeForTable($table);
            }
        }
    }

    /**
     * Store for a given table
     *
     * @param string $table
     * @return void
     */
    protected function storeForTable($table)
    {
        if ($this->isTableEnabled($table)) {
            $this->contentObject->start($this->getDataArray());
            /** @var StoreInDatabaseService $storeInDatabase */
            $storeInDatabase = $this->objectManager->get(StoreInDatabaseService::class);
            $storeInDatabase->setTable($table);
            $this->setPropertiesForTable($table, $storeInDatabase);
            $this->addArrayToDataArray(['uid_' . $table => $storeInDatabase->execute()]);
        }
    }

    /**
     * Store properties for a table
     *
     * @param string $table
     * @param StoreInDatabaseService $storeInDatabase
     * @return void
     */
    protected function setPropertiesForTable($table, StoreInDatabaseService $storeInDatabase)
    {
        foreach ($this->configuration[$table] as $field => $value) {
            if (!$this->isSkippedKey($field)) {
                $value = $this->contentObject->cObjGetSingle(
                    $this->configuration[$table][$field],
                    $this->configuration[$table][$field . '.']
                );
                $storeInDatabase->addProperty($field, $value);
            }
        }
    }

    /**
     * Check if this table is enabled in TypoScript
     *
     * @param string $table
     * @return bool
     */
    protected function isTableEnabled($table)
    {
        return $this->contentObject->cObjGetSingle(
            $this->configuration[$table]['_enable'],
            $this->configuration[$table]['_enable.']
        ) === '1';
    }

    /**
     * Should this key skipped because it starts with _ or ends with .
     *
     * @param string $key
     * @return bool
     */
    protected function isSkippedKey($key)
    {
        return StringUtility::startsWith($key, '_') || StringUtility::endsWith($key, '.');
    }

    /**
     * Add array to dataArray
     *
     * @param array $array
     * @return void
     */
    protected function addArrayToDataArray(array $array)
    {
        $dataArray = $this->getDataArray();
        $dataArray = array_merge($dataArray, $array);
        $this->setDataArray($dataArray);
    }

    /**
     * @return array
     */
    public function getDataArray()
    {
        return $this->dataArray;
    }

    /**
     * @param array $dataArray
     * @return SaveToAnyTableFinisher
     */
    public function setDataArray($dataArray)
    {
        $this->dataArray = $dataArray;
        return $this;
    }
}
