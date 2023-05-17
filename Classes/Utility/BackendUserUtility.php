<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Class BackendUserUtility
 */
class BackendUserUtility extends AbstractUtility
{
    /**
     * @return bool
     */
    public static function isAdminAuthentication()
    {
        $userAuthentication = self::getBackendUserAuthentication();
        $isUserBackendEnableLoginAs =
            $userAuthentication->getTSConfig()['tx_femanager.']['UserBackend.']['enableLoginAs'] ?? 0;

        return $userAuthentication->user['admin'] === 1 || (int)$isUserBackendEnableLoginAs === 1;
    }

    /**
     * @return BackendUserAuthentication
     */
    public static function getBackendUserAuthentication()
    {
        return parent::getBackendUserAuthentication();
    }
}
