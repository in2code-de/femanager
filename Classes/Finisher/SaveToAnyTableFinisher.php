<?php

declare(strict_types=1);
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Service\StoreInDatabaseService;
use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class SaveToAnyTableFinisher
 */
class SaveToAnyTableFinisher extends AbstractFinisher implements FinisherInterface
{
    /**
     * Inject a complete new content object
     *
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var TypoScriptService
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
     * @param ContentObjectRenderer $contentObject
     */
    public function injectContentObjectRenderer(ContentObjectRenderer $contentObject)
    {
        $this->contentObject = $contentObject;
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param TypoScriptService $typoScriptService
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }

    /**
     * Overwrite configuration with
     *      plugin.tx_femanager.settings.new.storeInDatabase
     */
    public function initializeFinisher()
    {
        if ($this->typoScriptService == null) {
            $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        }
        $configuration = $this->typoScriptService->convertPlainArrayToTypoScriptArray($this->settings);
        if (!empty($configuration['new.']['storeInDatabase.'])) {
            $this->configuration = $configuration['new.']['storeInDatabase.'];
        }
    }

    /**
     * Store to any table
     */
    public function storeFinisher()
    {
        if (!empty($this->configuration)) {
            $this->addArrayToDataArray($this->user->_getProperties());
            foreach ((array)array_keys($this->configuration) as $table) {
                $this->storeForTable($table);
            }
        }
    }

    /**
     * Store for a given table
     *
     * @param string $table
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
