<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Repository\UserGroupRepository;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class AbstractUtility
 */
abstract class AbstractUtility
{

    /**
     * Get table configuration array for a defined table
     *
     * @param string $table
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getTcaFromTable($table = 'fe_users')
    {
        if (!empty($GLOBALS['TCA'][$table])) {
            return $GLOBALS['TCA'][$table];
        }
        return [];
    }

    /**
     * @return DatabaseConnection
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getFilesArray()
    {
        return (array)$_FILES;
    }

    /**
     * @return UserGroupRepository
     */
    protected static function getUserGroupRepository()
    {
        return self::getObjectManager()->get(UserGroupRepository::class);
    }

    /**
     * @return TypoScriptFrontendController
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Get TYPO3 encryption key
     *
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getEncryptionKey()
    {
        if (empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])) {
            throw new \Exception('No encryption key found in this TYPO3 installation');
        }
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
    }

    /**
     * Get extension configuration from LocalConfiguration.php
     *
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getExtensionConfiguration(): array
    {
        $configuration = [];
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['femanager'])) {
            $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['femanager']);
        }
        return $configuration;
    }

    /**
     * @return ContentObjectRenderer
     */
    protected static function getContentObject()
    {
        return self::getObjectManager()->get(ContentObjectRenderer::class);
    }

    /**
     * @return ConfigurationManager
     */
    protected static function getConfigurationManager()
    {
        return self::getObjectManager()->get(ConfigurationManager::class);
    }

    /**
     * @return ObjectManagerInterface
     */
    protected static function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
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
