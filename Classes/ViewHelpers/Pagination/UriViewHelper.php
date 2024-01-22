<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Pagination;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

/**
 * UriViewHelper
 */
class UriViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'identifier important if more widgets on same page', false, 'widget');
        $this->registerArgument('arguments', 'array', 'Arguments', false, []);
    }

    /**
     * Build an uri to current action with &tx_ext_plugin[currentPage]=2
     *
     * @return string The rendered uri
     * @throws FluidViewHelperException
     */
    public function render(): string
    {
        if (! $this->renderingContext instanceof RenderingContext) {
            throw new FluidViewHelperException(
                'Something went wrong; RenderingContext should be available in ViewHelper',
                1638341671
            );
        }
        $uriBuilder = $this->renderingContext->getUriBuilder();
        $extensionName = $this->renderingContext->getRequest()->getControllerExtensionName();
        $pluginName = $this->renderingContext->getRequest()->getPluginName();
        $extensionService = GeneralUtility::makeInstance(ExtensionService::class);
        $pluginNamespace = $extensionService->getPluginNamespace($extensionName, $pluginName);
        $argumentPrefix = $pluginNamespace . '[' . $this->arguments['name'] . ']';
        $arguments = $this->hasArgument('arguments') ? $this->arguments['arguments'] : [];
        if ($this->hasArgument('action')) {
            $arguments['action'] = $this->arguments['action'];
        }
        if ($this->hasArgument('format') && $this->arguments['format'] !== '') {
            $arguments['format'] = $this->arguments['format'];
        }
        $uriBuilder->reset()
            ->setArguments([$argumentPrefix => $arguments])
            ->setAddQueryString(true)
            ->setArgumentsToBeExcludedFromQueryString([$argumentPrefix, 'cHash']);
        return $uriBuilder->build();
    }
}
