<?php

declare(strict_types=1);

namespace In2code\Femanager\ViewHelpers\Pagination;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

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
     */
    public function render(): string
    {
        $uriBuilder = $this->renderingContext->getControllerContext()->getUriBuilder();
        $extensionName = $this->renderingContext->getControllerContext()->getRequest()->getControllerExtensionName();
        $pluginName = $this->renderingContext->getControllerContext()->getRequest()->getPluginName();
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
        $addQueryStringMethod = $this->arguments['addQueryStringMethod'] ?? null;
        if (is_string($addQueryStringMethod)) {
            $uriBuilder->setAddQueryStringMethod($addQueryStringMethod);
        }
        return $uriBuilder->build();
    }
}
