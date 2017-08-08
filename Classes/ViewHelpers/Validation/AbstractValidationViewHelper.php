<?php
declare(strict_types=1);
namespace In2code\Femanager\ViewHelpers\Validation;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AbstractValidationViewHelper
 */
abstract class AbstractValidationViewHelper extends AbstractViewHelper
{

    /**
     * Get key for typoscript configuration "validation"
     *
     * @return string
     */
    protected function getValidationName()
    {
        $validationName = 'validation';

        // special case for second step in invitation
        if ($this->getControllerName() === 'invitation' &&
            $this->controllerContext->getRequest()->getControllerActionName() === 'edit') {
            $validationName = 'validationEdit';
        }

        return $validationName;
    }

    /**
     * Return controllername in lowercase
     *
     * @return string "new", "edit", "invitation"
     */
    protected function getControllerName()
    {
        return strtolower($this->controllerContext->getRequest()->getControllerName());
    }
}
