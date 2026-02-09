<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Misc;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CaptchaEnabledViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('settings', 'array', 'array $settings TypoScript', true);
    }

    public function render(): bool
    {
        $request = $this->getRequest();
        if (!$request) {
            return false;
        }

        $controllerName = strtolower((string)$request->getControllerName());
        $isCaptchaEnabled = (bool)($this->arguments['settings'][$controllerName]['validation']['captcha'] ?? false);
        return ExtensionManagementUtility::isLoaded('sr_freecap') && $isCaptchaEnabled;
    }

    private function getRequest(): ?ServerRequestInterface
    {
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }
        return null;
    }
}
