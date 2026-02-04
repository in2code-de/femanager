<?php

declare(strict_types=1);

namespace In2code\Femanager\DataProcessor;

use In2code\Femanager\Utility\ConfigurationUtility;
use Psr\Http\Message\RequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use UnexpectedValueException;

class DataProcessorRunner
{
    /**
     * Call classes after submit but before action
     *
     * @throws \Exception
     */
    public function callClasses(
        array $settings,
        ContentObjectRenderer $contentObject,
        Arguments $controllerArguments,
        RequestInterface $request
    ): void {
        foreach ($this->getClasses($settings, $request) as $configuration) {
            $class = $configuration['class'];
            if (!class_exists($class)) {
                throw new UnexpectedValueException(
                    'DataProcessor class ' . $class . ' does not exists - check if file is loaded correctly',
                    1516373818752
                );
            }

            if (!is_subclass_of($class, DataProcessorInterface::class)) {
                throw new UnexpectedValueException(
                    'Finisher does not implement ' . DataProcessorInterface::class,
                    1516373829946
                );
            }

            /** @var AbstractDataProcessor $dataProcessor */
            $dataProcessor = GeneralUtility::makeInstance(
                $class,
                $configuration['config'] ?? [],
                $settings,
                $contentObject,
                $controllerArguments
            );
            $dataProcessor->initializeDataProcessor();
            $dataProcessor->process();
        }
    }

    /**
     * Get all classes to this event from typoscript and sort them
     */
    protected function getClasses(array $settings, RequestInterface $request): array
    {
        /** @var ExtbaseRequestParameters|null $extbaseRequestParameter */
        $extbaseRequestParameter = $request->getAttribute('extbase');
        $controllerName = $extbaseRequestParameter?->getControllerName() ?? '';
        $actionName = $extbaseRequestParameter?->getControllerActionName() ?? '';

        $allDataProcessors = ConfigurationUtility::getValue('dataProcessors', $settings) ?? [];

        ksort($allDataProcessors);

        return array_filter($allDataProcessors, function (array $processor) use ($controllerName, $actionName) {
            $events = $processor['events'] ?? [];

            if (!isset($events[$controllerName])) {
                return false;
            }

            return GeneralUtility::inList($events[$controllerName], $actionName);
        });
    }
}
