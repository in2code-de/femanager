<?php

declare(strict_types=1);
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class ConfigurationUtility
 */
class ConfigurationUtility extends AbstractUtility
{

    const DEFAULT_CONFIGURATION = [
        'new./adminConfirmationRedirect' => '',
        'new./email./createUserNotify./sender./email./value' => '9999',
        'new./email./createUserNotify./sender./name./value' => '9999',
        'new./email./createUserNotify./subject' => 'TEXT',
        'new./email./createUserNotify./subject.' => [],
        'new./email./createUserNotify.' => [],
        'new./email./createUserConfirmation./sender./email./value' => '',
        'new./email./createUserConfirmation./subject' => 'TEXT',
        'new./email./createUserConfirmation./subject.' => [],
        'new./email./createUserConfirmation.' => [],
        'new./fillEmailWithUsername' => 0,
        'new/misc/passwordSave' => 0,
        'new./misc./passwordSave' => 0,
        'new./redirect' => 'TEXT',
        'new./redirect.' => [],
        'new./requestRedirect' => 'TEXT',
        'new./requestRedirect.' => [],
        'edit./redirect' => 'TEXT',
        'edit./redirect.' => [],
        'edit./requestRedirect' => 'TEXT',
        'edit./requestRedirect.' => [],
        'new./notifyAdmin' => '',
        'new./email./createAdminNotify./receiver./email./value' => '',
        'new./email./createAdminNotify./subject' => 'TEXT',
        'new./email./createAdminNotify./subject.' => [],
        'new./email./createAdminNotify.' => [],
        'new./login' => '0',
        'persistence./storagePid' => '0',
        '_enable' => '',
        '_enable.' => [],
        '_TypoScriptIncluded' => 0,
        'dataProcessors' => [],
        'edit/fillEmailWithUsername' => '0',
        'edit./forceValues./beforeAnyConfirmation.' => [],
        'edit/misc/passwordSave' => '',
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
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
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

    public static function IsCreateUserNotifyActive($config): bool
    {
        if (ConfigurationUtility::getValue(
                'new/email/createUserNotify/sender/email/value',
                $config
            ) && ConfigurationUtility::getValue(
                'new./email./createUserNotify./sender./name./value',
                $config
            )) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public static function getDefaultConfiguration($key)
    {
        return self::DEFAULT_CONFIGURATION[$key] ?? null;
    }

    public static function getValue($key, $config)
    {
        try {
            return ArrayUtility::getValueByPath($config, $key);
        } catch (MissingArrayPathException $ex) {
            return self::getDefaultConfiguration($key);
        }
    }
}
