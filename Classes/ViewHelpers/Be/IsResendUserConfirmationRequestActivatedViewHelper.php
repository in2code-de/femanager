<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Be;

use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @deprecated will be removed with TYPO3 V14.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class IsResendUserConfirmationRequestActivatedViewHelper extends AbstractViewHelper
{
    public function render(): bool
    {
        return ConfigurationUtility::isResendUserConfirmationRequestActive();
    }
}
