<?php

declare(strict_types=1);

namespace In2code\Femanager\Event;

use In2code\Femanager\Domain\Service\SendMailService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * @deprecated will be removed with version 14.0
 */
class BeforeMailBodyRenderEvent
{
    public function __construct(private StandaloneView $standAloneView, private readonly array $variables, private readonly SendMailService $service)
    {
    }

    public function getStandaloneView(): StandaloneView
    {
        return $this->standAloneView;
    }

    public function getService(): SendMailService
    {
        return $this->service;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setStandaloneView(StandaloneView $standAloneView): void
    {
        $this->standAloneView = $standAloneView;
    }
}
