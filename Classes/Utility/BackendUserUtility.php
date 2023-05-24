<?php

declare(strict_types=1);

namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $context = GeneralUtility::makeInstance(Context::class);
        if ($context->getPropertyFromAspect('backend.user', 'isAdmin') === true) {
            return true;
        }
        
        $tsConfigEnableLoginAs = (int)($userAuthentication->getTSConfig()['tx_femanager.']['UserBackend.']['enableLoginAs'] ?? 0);

        return $tsConfigEnableLoginAs === 1;
    }

    /**
     * @return BackendUserAuthentication
     */
    public static function getBackendUserAuthentication()
    {
        return parent::getBackendUserAuthentication();
    }
}
