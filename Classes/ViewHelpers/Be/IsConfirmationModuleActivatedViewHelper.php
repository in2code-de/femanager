<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Be;

use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class IsConfirmationModuleActivatedViewHelper
 */
class IsConfirmationModuleActivatedViewHelper extends AbstractViewHelper
{

    /**
     * @return bool
     */
    public function render(): bool
    {
        return ConfigurationUtility::isConfirmationModuleActive();
    }
}
