<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility as LocalizationUtilityExtbase;

/**
 * Class LocalizationUtility
 */
class LocalizationUtility extends LocalizationUtilityExtbase
{

    /**
     * Returns the localized label of the LOCAL_LANG key, but prefill extensionName
     *
     * @param string $key The key from the LOCAL_LANG array for which to return the value.
     * @param string $extensionName The name of the extension
     * @param array $arguments the arguments of the extension, being passed over to vsprintf
     * @return string|null
     */
    public static function translate($key, $extensionName = 'femanager', $arguments = null)
    {
        return parent::translate($key, $extensionName, $arguments);
    }

    /**
     * Get locallang translation with key prefix tx_femanager_domain_model_log.state.
     *
     * @param int $state
     * @return null|string
     */
    public static function translateByState($state)
    {
        return self::translate('tx_femanager_domain_model_log.state.' . $state);
    }
}
