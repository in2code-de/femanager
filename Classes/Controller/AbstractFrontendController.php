<?php

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Service\RatelimiterService;
use In2code\Femanager\Domain\Service\UserGroupSanitizationService;

abstract class AbstractFrontendController extends AbstractController
{
    /**
     * @var RatelimiterService
     */
    protected $ratelimiterService;

    /**
     * @var UserGroupSanitizationService
     */
    protected $userGroupSanitizationService;

    public function injectRatelimiterService(RatelimiterService $ratelimiterService): void
    {
        $this->ratelimiterService = $ratelimiterService;
    }

    public function injectUserGroupSanitizationService(
        UserGroupSanitizationService $userGroupSanitizationService
    ): void {
        $this->userGroupSanitizationService = $userGroupSanitizationService;
    }
}