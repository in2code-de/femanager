<?php

namespace In2code\Femanager\Controller;

abstract class AbstractFrontendController extends AbstractController
{
    public function __construct(protected \In2code\Femanager\Domain\Service\RatelimiterService $ratelimiterService)
    {
    }
}
