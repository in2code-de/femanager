<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

use In2code\Femanager\Utility\StringUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SaveToAnyTableFinisher extends AbstractFinisher implements FinisherInterface
{
    /**
     * Overwrite configuration with
     *      plugin.tx_femanager.settings.new.storeInDatabase
     */
    public function initializeFinisher(): void
    {
        $this->contentObject->start($this->user->_getProperties());
        $this->finisherConfiguration = $this->typoScriptService->convertPlainArrayToTypoScriptArray(
            $this->typoScriptSettings['new']['storeInDatabase'] ?? []
        );
    }

    /**
     * Store to any table
     */
    public function storeFinisher(): void
    {
        if (!empty($this->finisherConfiguration)) {
            foreach (array_keys($this->finisherConfiguration) as $table) {
                $this->storeForTable($table);
            }
        }
    }

    /**
     * Store for a given table
     */
    protected function storeForTable(string $table): void
    {
        if ($this->isTableEnabled($table)) {
            $propertiesToWrite = [];

            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
            $this->setPropertiesToWrite($propertiesToWrite, $table);
            $connection->insert($table, $propertiesToWrite);
        }
    }

    protected function setPropertiesToWrite(array &$propertiesToWrite, string $table): void
    {
        foreach ($this->finisherConfiguration[$table] as $field => $value) {
            if (!$this->isSkippedKey($field)) {
                $value = $this->contentObject->cObjGetSingle(
                    (string)$this->finisherConfiguration[$table][$field],
                    (array)$this->finisherConfiguration[$table][$field . '.']
                );
                $propertiesToWrite[$field] = $value;
            }
        }
    }

    /**
     * Check if this table is enabled in TypoScript
     */
    protected function isTableEnabled(string $table): bool
    {
        return $this->contentObject->cObjGetSingle(
            (string)$this->finisherConfiguration[$table]['_enable'],
            (array)$this->finisherConfiguration[$table]['_enable.']
        ) === '1';
    }

    /**
     * Should this key skipped because it starts with _ or ends with .
     *
     * @param string $key
     * @return bool
     */
    protected function isSkippedKey(string $key): bool
    {
        if (StringUtility::startsWith($key, '_')) {
            return true;
        }

        return (bool)StringUtility::endsWith($key, '.');
    }
}
