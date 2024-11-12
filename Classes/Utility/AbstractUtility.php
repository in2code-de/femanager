<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use Exception;
use In2code\Femanager\Domain\Repository\UserGroupRepository;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use UnexpectedValueException;

/**
 * Class AbstractUtility
 */
abstract class AbstractUtility
{
    /**
     * Get table configuration array for a defined table
     *
     * @param string $table
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getTcaFromTable($table = 'fe_users'): array
    {
        if (!empty($GLOBALS['TCA'][$table])) {
            return $GLOBALS['TCA'][$table];
        }

        return [];
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @codeCoverageIgnore
     */
    public static function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getFilesArray(): array
    {
        return $_FILES;
    }

    protected static function getUserGroupRepository(): UserGroupRepository
    {
        return GeneralUtility::makeInstance(UserGroupRepository::class);
    }

    /**
     * @return TypoScriptFrontendController|null
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'] ?? null;
    }

    /**
     * Get TYPO3 encryption key
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getEncryptionKey(): string
    {
        if (empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])) {
            throw new UnexpectedValueException('No encryption key found in this TYPO3 installation', 1516373945265);
        }

        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
    }

    /**
     * Get extension configuration from LocalConfiguration.php
     *
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected static function getExtensionConfiguration(): array
    {
        return (array)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('femanager');
    }

    /**
     * @throws Exception
     */
    protected static function getContentObject(): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * @codeCoverageIgnore
     * @throws Exception
     */
    protected static function getConfigurationManager(): ConfigurationManagerInterface
    {
        return GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
    }

    /**
     * @return BackendUserAuthentication
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }
}
