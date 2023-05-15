<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Validation;

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

/**
 * Class AbstractValidationViewHelper
 */
abstract class AbstractValidationViewHelper extends AbstractViewHelper
{
    /**
     * Get key for typoscript configuration "validation"
     *
     * @return string
     * @throws FluidViewHelperException
     */
    protected function getValidationName(): string
    {
        $validationName = 'validation';

        // special case for second step in invitation
        if (! $this->renderingContext instanceof RenderingContext) {
            throw new FluidViewHelperException(
                'Something went wrong; RenderingContext should be available in ViewHelper',
                1638341336
            );
        }

        if ($this->getControllerName() === 'invitation' &&
            $this->renderingContext->getRequest()->getControllerActionName() === 'edit') {
            $validationName = 'validationEdit';
        }

        return $validationName;
    }

    /**
     * Return controllername in lowercase
     *
     * @return string "new", "edit", "invitation"
     * @throws FluidViewHelperException
     */
    protected function getControllerName(): string
    {
        if (! $this->renderingContext instanceof RenderingContext) {
            throw new FluidViewHelperException(
                'Something went wrong; RenderingContext should be available in ViewHelper',
                1638341335
            );
        }
        return strtolower((string) $this->renderingContext->getRequest()->getControllerName());
    }
}
