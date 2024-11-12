<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\FinisherService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class FinisherRunner
 */
class FinisherRunner
{
    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * TypoScript settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * FinisherRunner constructor.
     */
    public function __construct(protected \In2code\Femanager\Domain\Service\FinisherService $finisherService)
    {
    }

    /**
     * Call finisher classes after submit
     */
    public function callFinishers(
        User $user,
        string $actionMethodName,
        array $settings,
        ContentObjectRenderer $contentObject
    ): void {
        foreach ($this->getFinisherClasses($settings) as $finisherSettings) {
            $this->finisherService->init($user, $settings, $contentObject);
            $this->finisherService->setClass($finisherSettings['class']);
            $this->finisherService->setRequirePath($finisherSettings['require'] ?? '');
            $this->finisherService->setConfiguration($finisherSettings['config'] ?? []);
            $this->finisherService->setActionMethodName($actionMethodName);
            $this->finisherService->start();
        }
    }

    /**
     * Get all finisher classes from typoscript and sort them
     */
    protected function getFinisherClasses(array $settings): array
    {
        $finishers = (array)$settings['finishers'];
        ksort($finishers);
        return $finishers;
    }
}
