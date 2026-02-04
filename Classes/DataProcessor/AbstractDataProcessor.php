<?php

declare(strict_types=1);

namespace In2code\Femanager\DataProcessor;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

abstract class AbstractDataProcessor implements DataProcessorInterface
{
    public function __construct(
        protected array $configuration,
        protected array $settings,
        protected ContentObjectRenderer $contentObject,
        protected Arguments $controllerArguments
    ) {
    }

    public function initializeDataProcessor()
    {
    }

    public function getConfiguration(string $path = ''): mixed
    {
        $configuration = $this->configuration;
        if ($path !== '' && $path !== '0') {
            return ArrayUtility::getValueByPath($configuration, $path, '.');
        }

        return $configuration;
    }
}
