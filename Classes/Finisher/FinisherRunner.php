<?php
declare(strict_types=1);
namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\FinisherService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class FinisherRunner
 */
class FinisherRunner
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

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
     * Call finisher classes after submit
     *
     * @param User $user
     * @param string $actionMethodName
     * @param array $settings
     * @param ContentObjectRenderer $contentObject
     * @return void
     */
    public function callFinishers(
        User $user,
        $actionMethodName,
        $settings,
        ContentObjectRenderer $contentObject
    ) {
        foreach ($this->getFinisherClasses($settings) as $finisherSettings) {
            /** @var FinisherService $finisherService */
            $finisherService = $this->objectManager->get(FinisherService::class, $user, $settings, $contentObject);
            $finisherService->setClass($finisherSettings['class']);
            $finisherService->setRequirePath((string)$finisherSettings['require']);
            $finisherService->setConfiguration((array)$finisherSettings['config']);
            $finisherService->setActionMethodName($actionMethodName);
            $finisherService->start();
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
