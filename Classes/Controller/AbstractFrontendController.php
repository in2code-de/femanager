<?php

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Domain\Service\RatelimiterService;
use In2code\Femanager\Domain\Service\SendMailService;
use In2code\Femanager\Finisher\FinisherRunner;
use In2code\Femanager\Utility\LogUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

abstract class AbstractFrontendController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserGroupRepository $userGroupRepository,
        protected PersistenceManager $persistenceManager,
        protected SendMailService $sendMailService,
        protected FinisherRunner $finisherRunner,
        protected LogUtility $logUtility,
        protected RatelimiterService $ratelimiterService
    ) {
    }
}
