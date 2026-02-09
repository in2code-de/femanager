<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Be;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetClassNameOnActionViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'actionName',
            'string',
            'action name to compare with current action',
            true
        );
        $this->registerArgument(
            'className',
            'string',
            'classname that should be returned if action fits',
            false,
            ' btn-info'
        );
        $this->registerArgument(
            'fallbackClassName',
            'string',
            'fallback classname if action does not fit',
            false,
            ''
        );
    }

    /**
     * Return className if actionName fits to current action
     */
    public function render(): string
    {
        $actionName = $this->arguments['actionName'];
        $className = $this->arguments['className'];
        $fallbackClassName = $this->arguments['fallbackClassName'];

        if ($this->getCurrentActionName() === $actionName) {
            return $className;
        }

        return $fallbackClassName;
    }

    protected function getCurrentActionName(): string
    {
        /** @var ExtbaseRequestParameters|null $extbaseRequestParameter */
        $extbaseRequestParameter = $this->renderingContext
            ?->getAttribute(ServerRequestInterface::class)
            ?->getAttribute('extbase');

        return $extbaseRequestParameter?->getControllerActionName() ?? '';
    }
}
