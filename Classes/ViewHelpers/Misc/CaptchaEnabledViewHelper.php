<?php

declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Misc;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

/**
 * Look if captcha is enabled
 */
class CaptchaEnabledViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('settings', 'bool', 'array $settings TypoScript', true);
    }

    /**
     * Check if captcha is enabled
     * @throws FluidViewHelperException
     */
    public function render(): bool
    {
        if (! $this->renderingContext instanceof RenderingContext) {
            throw new FluidViewHelperException(
                'Something went wrong; RenderingContext should be available in ViewHelper',
                1638341672
            );
        }
        $controllerName = strtolower((string) $this->renderingContext->getRequest()->getControllerName());

        return ExtensionManagementUtility::isLoaded('sr_freecap')
            && $this->templateVariableContainer->getByPath('settings.' . $controllerName . '.validation.captcha.captcha');
    }
}
