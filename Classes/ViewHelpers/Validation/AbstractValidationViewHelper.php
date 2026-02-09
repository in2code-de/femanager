<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Validation;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

abstract class AbstractValidationViewHelper extends AbstractViewHelper
{
    /**
     * Get key for typoscript configuration "validation"
     */
    protected function getValidationName(): string
    {
        $validationName = 'validation';
        $request = $this->getRequest();

        if (!$request) {
            throw new FluidViewHelperException('Request object is missing.', 1638341336);
        }

        if ($this->getControllerName() === 'invitation' && $request->getControllerActionName() === 'edit') {
            return 'validationEdit';
        }

        return $validationName;
    }

    /**
     * Return controllername in lowercase
     *
     * @return string "new", "edit", "invitation"
     */
    protected function getControllerName(): string
    {
       $request = $this->getRequest();
       if (!$request) {
           throw new FluidViewHelperException('Request object is missing.', 1638341336);
       }

        return strtolower((string)$request->getControllerName());
    }

    private function getRequest(): ?ServerRequestInterface
    {
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }
        return null;
    }
}
