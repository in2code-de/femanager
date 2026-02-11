<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

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

    public static function hasBackendUserAccessToFeUserStoragePage(User $user): bool
    {
        if (BackendUserUtility::isAdmin()) {
            return true;
        }

        // check if the current BE User has access to the page where the FE_User is stored
        return self::getBackendUserAuthentication()->doesUserHaveAccess(
            DatabaseUtility::getPageIgnoreEnableFields($user->getPid()),
            Permission::PAGE_SHOW
        );
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
