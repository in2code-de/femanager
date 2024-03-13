<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Service\SendMailService;
use TYPO3\CMS\Fluid\View\StandaloneView;

class BeforeMailBodyRenderEvent
{
    /**
     * @var StandaloneView
     */
    private $standAloneView;
    /**
     * @var array
     */
    private $variables;
    /**
     * @var SendMailService
     */
    private $service;

    public function __construct(StandaloneView $standAloneView, array $variables, SendMailService $service)
    {
        $this->standAloneView = $standAloneView;
        $this->variables = $variables;
        $this->service = $service;
    }

    /**
     * @return StandaloneView
     */
    public function getStandaloneView(): StandaloneView
    {
        return $this->standAloneView;
    }

    /**
     * @return SendMailService
     */
    public function getService(): SendMailService
    {
        return $this->service;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param StandaloneView $standAloneView
     */
    public function setStandaloneView(StandaloneView $standAloneView): void
    {
        $this->standAloneView = $standAloneView;
    }
}
