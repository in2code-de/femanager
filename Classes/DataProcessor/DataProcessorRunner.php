<?php
declare(strict_types=1);
namespace In2code\Femanager\DataProcessor;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class DataProcessorRunner
 */
class DataProcessorRunner
{

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * TypoScript settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $interface = DataProcessorInterface::class;

    /**
     * Call classes after submit but before action
     *
     * @param array $arguments
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     * @param Arguments $controllerArguments
     * @return array
     * @throws \Exception
     */
    public function callClasses(
        array $arguments,
        array $settings,
        ContentObjectRenderer $contentObject,
        Arguments $controllerArguments
    ): array {
        foreach ($this->getClasses($settings) as $configuration) {
            $class = $configuration['class'];
            if (!class_exists($class)) {
                throw new \Exception(
                    'DataProcessor class ' . $class . ' does not exists - check if file is loaded correctly'
                );
            }
            if (is_subclass_of($class, $this->interface)) {
                /** @var AbstractDataProcessor $dataProcessor */
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $dataProcessor = ObjectUtility::getObjectManager()->get(
                    $class,
                    (array)$configuration['config'],
                    $settings,
                    $contentObject,
                    $controllerArguments
                );
                $dataProcessor->initializeDataProcessor();
                $arguments = $dataProcessor->process($arguments);
            } else {
                throw new \Exception('Finisher does not implement ' . $this->interface);
            }
        }
        return $arguments;
    }

    /**
     * Get all classes to this event from typoscript and sort them
     *
     * @param array $settings
     * @return array
     */
    protected function getClasses($settings): array
    {
        $allDataProcessors = (array)$settings['dataProcessors'];
        ksort($allDataProcessors);
        $dataProcessors = [];
        foreach ($allDataProcessors as $dataProcessor) {
            if (!empty($dataProcessor['events'])) {
                foreach ($dataProcessor['events'] as $controllerName => $actionList) {
                    if ($controllerName === FrontendUtility::getControllerName()) {
                        if (GeneralUtility::inList($actionList, FrontendUtility::getActionName())) {
                            $dataProcessors[] = $dataProcessor;
                        }
                    }
                }
            }
        }
        return $dataProcessors;
    }
}
