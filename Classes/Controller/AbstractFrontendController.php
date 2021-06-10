<?php
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Service\RatelimiterService;

abstract class AbstractFrontendController extends AbstractController
{
    /**
     * @var \In2code\Femanager\Domain\Service\RatelimiterService
     */
    protected $ratelimiterService;

    public function injectRatelimiterService(RatelimiterService $ratelimiterService): void
    {
        $this->ratelimiterService = $ratelimiterService;
    }
}
