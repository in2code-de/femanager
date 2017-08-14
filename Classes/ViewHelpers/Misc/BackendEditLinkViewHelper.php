<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Misc;

use In2code\Femanager\Utility\BackendUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class BackendEditLinkViewHelper
 */
class BackendEditLinkViewHelper extends AbstractViewHelper
{

    /**
     * Get an URI for backend edit
     *
     * @param string $tableName
     * @param int $identifier
     * @param bool $addReturnUrl
     * @return string
     */
    public function render(string $tableName, int $identifier, bool $addReturnUrl = true): string
    {
        return BackendUtility::getBackendEditUri($tableName, $identifier, $addReturnUrl);
    }
}
