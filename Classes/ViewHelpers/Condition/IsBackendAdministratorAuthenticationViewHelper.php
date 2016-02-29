<?php
namespace In2code\Femanager\ViewHelpers\Condition;

use In2code\Femanager\Utility\BackendUserUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class IsBackendAdministratorAuthenticationViewHelper
 * @package In2code\Femanager\ViewHelpers\Condition
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
