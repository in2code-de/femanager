<?php
declare(strict_types=1);
namespace In2code\Femanager\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendUtility
 */
class BackendUtility
{

    /**
     * @return int
     */
    public static function getPageIdentifier(): int
    {
        return (int)GeneralUtility::_GET('id');
    }
}
