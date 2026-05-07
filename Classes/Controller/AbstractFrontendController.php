<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Repository\UserGroupRepository;
use In2code\Femanager\Domain\Repository\UserRepository;
use In2code\Femanager\Domain\Service\RatelimiterService;
use In2code\Femanager\Domain\Service\SendMailService;
use In2code\Femanager\Domain\Service\ValidationService;
use In2code\Femanager\Finisher\FinisherRunner;
use In2code\Femanager\Utility\LogUtility;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Mvc\Controller\Exception\RequiredArgumentMissingException;
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
        protected RatelimiterService $ratelimiterService,
        protected ValidationService $validationService,
    ) {
    }

    protected function handleArgumentMappingExceptions(\Exception $exception): void
    {
        if ($exception instanceof RequiredArgumentMissingException) {
            $actionName = $this->request->getControllerActionName();
            $fallbackActions = $this->getArgumentMissingFallbackActions();
            if (isset($fallbackActions[$actionName])) {
                throw new PropagateResponseException(
                    $this->redirect($fallbackActions[$actionName]),
                    1746612001
                );
            }
        }
        parent::handleArgumentMappingExceptions($exception);
    }

    protected function getArgumentMissingFallbackActions(): array
    {
        return [];
    }
}
