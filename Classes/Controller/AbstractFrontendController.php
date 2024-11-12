<?php

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Service\RatelimiterService;

abstract class AbstractFrontendController extends AbstractController
{
    public function __construct(protected \In2code\Femanager\Domain\Service\RatelimiterService $ratelimiterService)
    {
    }
}
