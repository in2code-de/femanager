<?php

declare(strict_types=1);

namespace In2code\Femanager\DataProcessor;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AbstractDataProcessor
 */
abstract class AbstractDataProcessor implements DataProcessorInterface
{
    /**
     * AbstractDataProcessor constructor.
     */
    public function __construct(protected array $configuration, protected array $settings, protected \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject, protected \TYPO3\CMS\Extbase\Mvc\Controller\Arguments $controllerArguments)
    {
    }

    public function initializeDataProcessor()
    {
    }

    /**
     * @return mixed
     */
    public function getConfiguration(string $path = '')
    {
        $configuration = $this->configuration;
        if ($path !== '' && $path !== '0') {
            return ArrayUtility::getValueByPath($configuration, $path, '.');
        }

        return $configuration;
    }
}
