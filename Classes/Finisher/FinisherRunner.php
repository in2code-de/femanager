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
     * @var FinisherService
     */
    protected $finisherService;

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
     * @param FinisherService $finisherService
     */
    public function __construct(
        FinisherService $finisherService
    ) {
        $this->finisherService = $finisherService;
    }

    /**
     * Call finisher classes after submit
     *
     * @param User $user
     * @param string $actionMethodName
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     */
    public function callFinishers(
        User $user,
        $actionMethodName,
        $settings,
        ContentObjectRenderer $contentObject
    ) {
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
     *
     * @param array $settings
     * @return array
     */
    protected function getFinisherClasses($settings)
    {
        $finishers = (array)$settings['finishers'];
        ksort($finishers);
        return $finishers;
    }
}
