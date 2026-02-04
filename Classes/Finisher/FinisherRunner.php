<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Exception\ClassNotFoundException;
use In2code\Femanager\Exception\InterfaceNotImplementedException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FinisherRunner
{
    public function callFinishers(
        User $user,
        string $actionMethodName,
        array $settings,
        ContentObjectRenderer $contentObject
    ): void {
        foreach ($this->getFinisherClasses($settings) as $finisherSettings) {
            $class = $finisherSettings['class'];
            $this->requireFile($finisherSettings);

            if (!class_exists($class)) {
                throw new ClassNotFoundException(
                    'Class ' . $class . ' does not exists - check if file was loaded with autoloader',
                    1516373888508
                );
            }

            if (!is_subclass_of($class, FinisherInterface::class)) {
                throw new InterfaceNotImplementedException(
                    'Finisher does not implement ' . FinisherInterface::class,
                    1516373899775
                );
            }

            /** @var AbstractFinisher $finisher */
            $finisher = GeneralUtility::makeInstance(
                $class,
                $user,
                $finisherSettings,
                $settings,
                $actionMethodName,
                $contentObject
            );
            $finisher->initializeFinisher();
            $this->callFinisherMethods($finisher);
        }
    }

    /**
     * Get all finisher classes from TypoScript and sort them
     */
    protected function getFinisherClasses(array $settings): array
    {
        $finishers = (array)$settings['finishers'];
        ksort($finishers);
        return $finishers;
    }

    /**
     * Call methods in finisher class
     *      *Finisher()
     */
    protected function callFinisherMethods(AbstractFinisher $finisher): void
    {
        foreach (get_class_methods($finisher) as $method) {
            if (str_ends_with($method, 'Finisher') && !str_starts_with($method, 'initialize')) {
                $this->callInitializeFinisherMethod($finisher, $method);
                $finisher->{$method}();
            }
        }
    }

    /**
     * Call initializeFinisherMethods like "initializeUploadFinisher()"
     */
    protected function callInitializeFinisherMethod(AbstractFinisher $finisher, string $finisherMethod): void
    {
        if (method_exists($finisher, 'initialize' . ucfirst($finisherMethod))) {
            $finisher->{'initialize' . ucfirst($finisherMethod)}();
        }
    }

    protected function requireFile(array $finisherSettings): void
    {
        if (!empty($finisherSettings['require']) && file_exists($finisherSettings['require'])) {
            require_once($finisherSettings['require']);
        }
    }
}
