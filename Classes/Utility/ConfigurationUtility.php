<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ConfigurationUtility
 */
class ConfigurationUtility extends AbstractUtility
{

    const DEFAULT_CONFIGURATION = [
        'new./email./createUserNotify./sender./email./value' => 0,
        'new./email./createUserNotify./sender./name./value' => 0,
        'new./email./createUserNotify./subject' => 'TEXT',
        'new./email./createUserNotify./subject.' => [],
        'new./email./createUserNotify.' => [],
        'new./fillEmailWithUsername' => 0,
        'new/misc/passwordSave' => 0,
        'new./misc./passwordSave' => 0,
    ];

    /**
     * @return bool
     */
    public static function isDisableModuleActive(): bool
    {
        $configuration = self::getExtensionConfiguration();

        return $configuration['disableModule'] === '1';
    }

    /**
     * @return bool
     */
    public static function isConfirmationModuleActive(): bool
    {
        $configuration = self::getExtensionConfiguration();

        return $configuration['enableConfirmationModule'] === '1';
    }

    /**
     * @return bool
     */
    public static function isDisableLogActive(): bool
    {
        $configuration = self::getExtensionConfiguration();

        return $configuration['disableLog'] === '1';
    }

    /**
     * Get complete Typoscript or only a special value by a given path
     *
     * @param string $path "misc.uploadFolder" or empty for complete TypoScript array
     * @return string
     * @codeCoverageIgnore
     */
    public static function getConfiguration(string $path = '')
    {
        $configurationManager = ObjectUtility::getObjectManager()->get(ConfigurationManagerInterface::class);
        $typoscript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Femanager',
            'Pi1'
        );
        if (!empty($path)) {
            $typoscript = ArrayUtility::getValueByPath($typoscript, $path, '.');
        }

        return $typoscript;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public static function isBackendModuleFilterUserConfirmation(): bool
    {
        $config = BackendUserUtility::getBackendUserAuthentication()->getTSConfig(
            )['tx_femanager.']['UserBackend.']['confirmation.']['filter.']['userConfirmation'] ?? false;

        return (bool)$config;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public static function IsResendUserConfirmationRequestActive(): bool
    {
        $config = BackendUserUtility::getBackendUserAuthentication()->getTSConfig(
            )['tx_femanager.']['UserBackend.']['confirmation.']['ResendUserConfirmationRequest'] ?? false;
        return (bool)$config;
    }

    /**
     * @return mixed
     */
    public static function getDefaultConfiguration($key)
    {
        return self::DEFAULT_CONFIGURATION[$key];
    }

    public static function getValue($key, $config)
    {
        try {
            $value = ArrayUtility::getValueByPath($config, $key);
            return $value;
        } catch (MissingArrayPathException $ex) {
            return self::getDefaultConfiguration($key);
        }
    }
}
