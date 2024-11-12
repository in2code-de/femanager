<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Service\StoreInDatabaseService;
use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SaveToAnyTableFinisher
 */
class SaveToAnyTableFinisher extends AbstractFinisher implements FinisherInterface
{
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

    public function __construct(/**
     * Inject a complete new content object
     */
        protected \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject,
        \TYPO3\CMS\Core\TypoScript\TypoScriptService $typoScriptService
    ) {
        $this->typoScriptService = $typoScriptService;
    }

    /**
     * Overwrite configuration with
     *      plugin.tx_femanager.settings.new.storeInDatabase
     */
    public function initializeFinisher(): void
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
    public function storeFinisher(): void
    {
        if (!empty($this->configuration)) {
            $this->addArrayToDataArray($this->user->_getProperties());
            foreach (array_keys($this->configuration) as $table) {
                $this->storeForTable($table);
            }
        }
    }

    /**
     * Store for a given table
     */
    protected function storeForTable(string $table)
    {
        if ($this->isTableEnabled($table)) {
            $this->contentObject->start($this->getDataArray());
            /** @var StoreInDatabaseService $storeInDatabase */
            $storeInDatabase = GeneralUtility::makeInstance(StoreInDatabaseService::class);
            $storeInDatabase->setTable($table);
            $this->setPropertiesForTable($table, $storeInDatabase);
            $this->addArrayToDataArray(['uid_' . $table => $storeInDatabase->execute()]);
        }
    }

    /**
     * Store properties for a table
     *
     * @param string $table
     */
    protected function setPropertiesForTable($table, StoreInDatabaseService $storeInDatabase)
    {
        foreach ($this->configuration[$table] as $field => $value) {
            if (!$this->isSkippedKey($field)) {
                $value = $this->contentObject->cObjGetSingle(
                    (string)$this->configuration[$table][$field],
                    (array)$this->configuration[$table][$field . '.']
                );
                $storeInDatabase->addProperty($field, $value);
            }
        }
    }

    /**
     * Check if this table is enabled in TypoScript
     *
     * @param string $table
     */
    protected function isTableEnabled($table): bool
    {
        return $this->contentObject->cObjGetSingle(
            (string)$this->configuration[$table]['_enable'],
            (array)$this->configuration[$table]['_enable.']
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
        if (StringUtility::startsWith($key, '_')) {
            return true;
        }

        return (bool)StringUtility::endsWith($key, '.');
    }

    /**
     * Add array to dataArray
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
     */
    public function setDataArray($dataArray): static
    {
        $this->dataArray = $dataArray;
        return $this;
    }
}
