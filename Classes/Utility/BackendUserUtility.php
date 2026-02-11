<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendUserUtility extends AbstractUtility
{
    /**
     * @deprecated will be removed with V14. use BackendUserUtility::isAdmin() instead
     * @return bool
     */
    public static function isAdminAuthentication(): bool
    {
        trigger_error('will be removed with V14. use BackendUserUtility::isAdmin() instead', E_USER_DEPRECATED);
        return self::isAdmin();
    }

    public static function isAdmin(): bool
    {
        return self::getBackendUserAuthentication()->isAdmin();
    }

    /**
     * @return BackendUserAuthentication
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
