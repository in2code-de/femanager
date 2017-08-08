<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Look if captcha is enabled
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class CaptchaEnabledViewHelper extends AbstractViewHelper
{

    /**
     * Check if captcha is enabled
     *
     * @param array $settings TypoScript
     * @return bool
     */
    public function render($settings): bool
    {
        $controllerName = strtolower($this->controllerContext->getRequest()->getControllerName());
        return ExtensionManagementUtility::isLoaded('sr_freecap')
            && !empty($settings[$controllerName]['validation']['captcha']['captcha']);
    }
}
