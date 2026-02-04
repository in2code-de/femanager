<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

use In2code\Femanager\Domain\Model\User;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

abstract class AbstractFinisher implements FinisherInterface
{
    protected User $user;
    protected array $typoScriptSettings = [];
    protected array $finisherConfiguration = [];
    protected ?TypoScriptService $typoScriptService = null;

    /**
     * Controller actionName - usually "createAction" or "confirmationAction"
     */
    protected string $actionMethodName = '';

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser($user): FinisherInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getSettings(): array
    {
        return $this->typoScriptSettings;
    }

    public function setSettings($settings): FinisherInterface
    {
        $this->typoScriptSettings = $settings;
        return $this;
    }

    public function getFinisherConfiguration(): array
    {
        return $this->finisherConfiguration;
    }

    public function setFinisherConfiguration(array $finisherConfiguration): FinisherInterface
    {
        $this->finisherConfiguration = $finisherConfiguration;
        return $this;
    }

    public function getActionMethodName(): string
    {
        return $this->actionMethodName;
    }

    public function setActionMethodName(string $actionMethodName): FinisherInterface
    {
        $this->actionMethodName = $actionMethodName;
        return $this;
    }

    public function initializeFinisher(): void
    {
    }

    public function __construct(
        User $user,
        array $finisherConfiguration,
        array $typoScriptSettings,
        string $actionMethodName,
        protected ContentObjectRenderer $contentObject
    ) {
        $this->setUser($user);
        $this->setFinisherConfiguration($finisherConfiguration);
        $this->setSettings($typoScriptSettings);
        $this->setActionMethodName($actionMethodName);
        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
    }
}
