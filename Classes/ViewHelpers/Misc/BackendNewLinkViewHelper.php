<?php
declare(strict_types = 1);
namespace In2code\Femanager\ViewHelpers\Misc;

use In2code\Femanager\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class BackendNewLinkViewHelper
 */
class BackendNewLinkViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('tableName', 'string', 'Records table name (like "fe_users")', true);
        $this->registerArgument('addReturnUrl', 'bool', 'Add current URI as returnUrl', false, true);
    }

    /**
     * Get an URI for new records in backend
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        return BackendUtility::getBackendNewUri($arguments['tableName'], BackendUtility::getPageIdentifier(), $arguments['addReturnUrl']);
    }
}
