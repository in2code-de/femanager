<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Condition;

use In2code\Femanager\Utility\BackendUserUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class IsBackendAdministratorAuthenticationViewHelper
 */
class IsBackendAdministratorAuthenticationViewHelper extends AbstractViewHelper
{

    /**
     * Check if a backenduser-administrator is logged in
     *
     * @return bool
     */
    public function render()
    {
        return BackendUserUtility::isAdminAuthentication();
    }

}
