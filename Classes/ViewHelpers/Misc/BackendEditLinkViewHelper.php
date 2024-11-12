<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Misc;

use In2code\Femanager\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class BackendEditLinkViewHelper
 */
class BackendEditLinkViewHelper extends AbstractViewHelper
{
    /**
     * initialize arguments
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('tableName', 'string', 'Records table name (like "fe_users")', true);
        $this->registerArgument('identifier', 'integer', 'Record identifier to edit', true);
        $this->registerArgument('addReturnUrl', 'bool', 'Add current URI as returnUrl', false, true);
    }

    /**
     * Get an URI for backend edit
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(): string
    {
        return BackendUtility::getBackendEditUri(
            $this->arguments['tableName'],
            $this->arguments['identifier'],
            $this->arguments['addReturnUrl']
        );
    }
}
