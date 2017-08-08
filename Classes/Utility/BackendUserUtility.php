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
        return self::getBackendUserAuthentication()->user['admin'] === 1;
    }

    /**
     * @return BackendUserAuthentication
     */
    public static function getBackendUserAuthentication()
    {
        return parent::getBackendUserAuthentication();
    }
}
