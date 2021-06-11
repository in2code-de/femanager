<?php
declare(strict_types = 1);
namespace In2code\Femanager\ViewHelpers\Be;

use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class IsConfirmationModuleActivatedViewHelper
 */
class IsResendUserConfirmationRequestActivatedViewHelper extends AbstractViewHelper
{

    /**
     * @return bool
     */
    public function render(): bool
    {
        return ConfigurationUtility::IsResendUserConfirmationRequestActive();
    }
}
