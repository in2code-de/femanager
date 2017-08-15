<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Misc;

use In2code\Femanager\Utility\BackendUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class BackendNewLinkViewHelper
 */
class BackendNewLinkViewHelper extends AbstractViewHelper
{

    /**
     * Get an URI for new records in backend
     *
     * @param string $tableName
     * @param bool $addReturnUrl
     * @return string
     */
    public function render(string $tableName, bool $addReturnUrl = true): string
    {
        return BackendUtility::getBackendNewUri($tableName, BackendUtility::getPageIdentifier(), $addReturnUrl);
    }
}
