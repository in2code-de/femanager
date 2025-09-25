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
    final public const DEFAULT_CONFIGURATION = [
        '_TypoScriptIncluded' => 0,
        '_enable' => '',
        '_enable.' => [],
        'dataProcessors' => [],
        'edit./forceValues./beforeAnyConfirmation.' => [],
        'edit./redirect' => 'TEXT',
        'edit./redirect.' => [],
        'edit./requestRedirect' => 'TEXT',
        'edit./requestRedirect.' => [],
        'edit/fillEmailWithUsername' => '0',
        'edit/notifyAdmin' => '0',
        'edit/email/createUserNotify/notifyAdmin/receiver/email/value' => '',
        'edit/misc/passwordSave' => '',
        'invitation/misc/passwordSave' => '',
        'invitation./email./invitationAdminNotify.' => [],
        'invitation./email./invitationAdminNotifyStep1.' => [],
        'invitation./email./invitationRefused.' => [],
        'invitation./forceValues./beforeAnyConfirmation.' => [],
        'invitation./redirect' => 'TEXT',
        'invitation./redirect.' => [],
        'invitation./redirectDelete' => 'TEXT',
        'invitation./redirectDelete.' => [],
        'invitation./redirectStep1' => 'TEXT',
        'invitation./redirectStep1.' => [],
        'invitation./redirectPasswordChanged' => 'TEXT',
        'invitation./redirectPasswordChanged.' => [],
        'invitation./requestRedirect' => 'TEXT',
        'invitation./requestRedirect.' => [],
        'invitation/fillEmailWithUsername' => '0',
        'invitation/notifyAdmin' => 0,
        'invitation/email/invitationAdminNotify/receiver/name/value' => 'femanager',
        'invitation/email/invitationRefused/receiver/name/value' => 'femanager',
        'invitation/notifyAdminStep1' => 0,
        'invitation/email/invitationAdminNotifyStep1/receiver/name/value' => 'femanager',
        'new./adminConfirmationRedirect' => '',
        'new./email./createAdminConfirmation.' => [],
        'new./email./createAdminNotify.' => [],
        'new./email./createAdminNotify./receiver./email./value' => '',
        'new./email./createAdminNotify./subject' => 'TEXT',
        'new./email./createAdminNotify./subject.' => [],
        'new./email./createUserConfirmation.' => [],
        'new./email./createUserConfirmation./sender./email./value' => '',
        'new./email./createUserConfirmation./sender./name./value' => '',
        'new./email./createUserConfirmation./subject' => 'TEXT',
        'new./email./createUserConfirmation./subject.' => [],
        'new./email./createUserConfirmation./confirmUserConfirmation' => '0',
        'new./email./createUserConfirmation./confirmUserConfirmationRefused' => '0',
        'new./email./createUserConfirmation./confirmAdminConfirmation' => '0',
        'new./email./createUserNotify.' => [],
        'new./email./createUserNotify./sender./email./value' => '9999',
        'new./email./createUserNotify./sender./name./value' => '9999',
        'new./email./createUserNotify./subject' => 'TEXT',
        'new./email./createUserNotify./subject.' => [],
        'new./email./createUserNotifyRefused.' => [],
        'new./fillEmailWithUsername' => 0,
        'new./forceValues./beforeAnyConfirmation.' => [],
        'new./forceValues./onAdminConfirmation.' => [],
        'new./forceValues./onUserConfirmation.' => [],
        'new./login' => '0',
        'new./misc./passwordSave' => 0,
        'new./notifyAdmin' => '',
        'new./redirect' => 'TEXT',
        'new./redirect.' => [],
        'new./requestRedirect' => 'TEXT',
        'new./requestRedirect.' => [],
        'new./userConfirmationRedirect' => 'TEXT',
        'new./userConfirmationRedirect.' => [],
        'new/misc/passwordSave' => 0,
        'persistence./storagePid' => '0',
        'receiver./email' => 'TEXT',
        'receiver./email.' => [],
        'receiver./name' => 'TEXT',
        'receiver./name.' => [],
        'sender./email' => 'TEXT',
        'sender./email.' => [],
        'sender./name' => 'TEXT',
        'sender./name.' => [],
        'priority' => 'TEXT',
        'priority.' => [],
    ];

    public static function isDisableModuleActive(): bool
    {
        $configuration = self::getExtensionConfiguration();

        return $configuration['disableModule'] === '1';
    }

    public static function isConfirmationModuleActive(): bool
    {
        $configuration = self::getExtensionConfiguration();

        return $configuration['enableConfirmationModule'] === '1';
    }

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
    public static function getConfiguration(string $path = '', string $pluginName = null)
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $typoscript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Femanager',
            $pluginName
        );
        if (!empty($path)) {
            $typoscript = ArrayUtility::getValueByPath($typoscript, $path, '.');
        }

        return $typoscript;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function isBackendModuleFilterUserConfirmation(): bool
    {
        $config = BackendUserUtility::getBackendUserAuthentication()->getTSConfig(
        )['tx_femanager.']['UserBackend.']['confirmation.']['filter.']['userConfirmation'] ?? false;

        return (bool)$config;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function isResendUserConfirmationRequestActive(): bool
    {
        $config = BackendUserUtility::getBackendUserAuthentication()->getTSConfig(
        )['tx_femanager.']['UserBackend.']['confirmation.']['ResendUserConfirmationRequest'] ?? false;
        return (bool)$config;
    }

    public static function isCreateUserNotifyActive($config): bool
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
        } catch (MissingArrayPathException) {
            return self::getDefaultConfiguration($key);
        }
    }

    public static function notifyAdminAboutEdits($config)
    {
        if (self::getValue(
            'edit/email/notifyAdmin',
            $config
        ) || self::getValue(
            'edit/email/notifyAdmin/receiver/email/value',
            $config
        )) {
            return true;
        }
        return false;
    }
}
