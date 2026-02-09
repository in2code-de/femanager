<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Condition;

use In2code\Femanager\Utility\BackendUserUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 * @deprecated will be removed with TYPO3 V14
 */
class IsBackendAdministratorAuthenticationViewHelper extends AbstractViewHelper
{
    /**
     * Check if a backenduser-administrator is logged in
     */
    public function render(): bool
    {
        return BackendUserUtility::isAdminAuthentication();
    }
}
