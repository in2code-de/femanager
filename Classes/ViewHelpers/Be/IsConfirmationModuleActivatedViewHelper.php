<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Be;

use In2code\Femanager\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @deprecated will be removed with V14
 */
class IsConfirmationModuleActivatedViewHelper extends AbstractViewHelper
{
    public function render(): bool
    {
        return ConfigurationUtility::isConfirmationModuleActive();
    }
}
