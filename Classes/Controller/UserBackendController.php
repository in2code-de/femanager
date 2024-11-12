<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Event\AdminConfirmationUserEvent;
use In2code\Femanager\Event\RefuseUserEvent;
use In2code\Femanager\Utility\BackendUserUtility;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

/**
 * Class UserBackendController
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserBackendController extends AbstractController
{
    protected int $configPID;

    protected ModuleTemplate $moduleTemplate;

    public function __construct(protected ModuleTemplateFactory $moduleTemplateFactory)
    {
    }

    protected function initializeAction(): void
    {
        parent::initializeAction();
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle('Femanager');
        $this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
    }

    /**
     * @throws RouteNotFoundException
     * @throws AspectNotFoundException
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function listAction(array $filter = []): ResponseInterface
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->moduleTemplate->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackend($filter),
                'moduleUri' => $uriBuilder->buildUriFromRoute('tce_db'),
                'action' => 'list',
                'loginAsEnabled' => $this->loginAsEnabled(),
                'configPID' => $this->getConfigPID(),
            ]
        );
        return $this->moduleTemplate->renderResponse('UserBackend/List');
    }

    public function confirmationAction(array $filter = []): ResponseInterface
    {
        $this->configPID = $this->getConfigPID();

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->moduleTemplate->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackendForConfirmation(
                    $filter,
                    ConfigurationUtility::isBackendModuleFilterUserConfirmation()
                ),
                'moduleUri' => $uriBuilder->buildUriFromRoute('tce_db'),
                'action' => 'confirmation',
            ]
        );
        return $this->moduleTemplate->renderResponse('UserBackend/Confirmation');
    }

    public function userLogoutAction(User $user): ResponseInterface
    {
        if ($this->checkPageAndUserAccess($user) === false) {
            return new ForwardResponse('list');
        }

        UserUtility::removeFrontendSessionToUser($user);
        $this->addFlashMessage('User successfully logged out');
        return $this->redirect('list');
    }

    public function confirmUserAction(int $userIdentifier): ResponseInterface
    {
        $this->configPID = $this->getConfigPID();

        $user = $this->userRepository->findByUid($userIdentifier);

        if ($this->checkPageAndUserAccess($user) === false) {
            return new ForwardResponse('list');
        }

        $this->eventDispatcher->dispatch(new AdminConfirmationUserEvent($user));

        $jsonResult = $this->getFrontendRequestResult('adminConfirmation', $userIdentifier, $user);

        if ($jsonResult['status'] ?? false) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'BackendConfirmationFlashMessageConfirmed',
                    'femanager',
                    [$user->getUsername()]
                ),
                'User Confirmation'
            );
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'BackendConfirmationFlashMessageFailed',
                    'femanager',
                    [$user->getUsername()]
                ),
                'User Confirmation',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this->redirect('confirmation');
    }

    private function checkPageAndUserAccess($user): bool
    {
        if ($user === null) {
            return false;
        }

        if (BackendUserUtility::isAdminAuthentication() === false) {
            // check if the current BE User has access to the page where the FE_User is stored
            $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
            $pageRow = $pageRepository->getPage($user->getPid());
            if ($GLOBALS['BE_USER']->doesUserHaveAccess(
                $pageRow,
                Permission::PAGE_SHOW
            ) === false) {
                return false;
            }
        }

        return true;
    }

    public function refuseUserAction(int $userIdentifier): ResponseInterface
    {
        $this->configPID = $this->getConfigPID();

        $user = $this->userRepository->findByUid($userIdentifier);

        if ($this->checkPageAndUserAccess($user) === false) {
            return new ForwardResponse('list');
        }

        $this->eventDispatcher->dispatch(new RefuseUserEvent($user));

        $jsonResult = $this->getFrontendRequestResult('adminConfirmationRefused', $userIdentifier, $user);

        if ($jsonResult['status'] ?? false) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'BackendConfirmationFlashMessageRefused',
                    'femanager',
                    [$user->getUsername()]
                )
            );
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'BackendConfirmationFlashMessageFailed',
                    'femanager',
                    [$user->getUsername()]
                ),
                'User Confirmation',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this->redirect('confirmation');
    }

    public function listOpenUserConfirmationsAction(array $filter = []): ResponseInterface
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->moduleTemplate->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackendForConfirmation(
                    $filter,
                    false
                ),
                'moduleUri' => $uriBuilder->buildUriFromRoute('tce_db'),
                'action' => 'listOpenUserConfirmations',
            ]
        );
        return $this->moduleTemplate->renderResponse('UserBackend/ListOpenUserConfirmations');
    }

    public function resendUserConfirmationRequestAction(int $userIdentifier): ResponseInterface
    {
        $user = $this->userRepository->findByUid($userIdentifier);

        if ($this->checkPageAndUserAccess($user) === false) {
            return new ForwardResponse('list');
        }

        $this->sendCreateUserConfirmationMailFromBackend($user);
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'BackendConfirmationFlashMessageReSend',
                'femanager',
                [$user->getUsername()]
            ),
            '',
            ContextualFeedbackSeverity::INFO
        );
        return $this->redirect('listOpenUserConfirmations');
    }

    public function getConfigPID(): int
    {
        if (isset($this->moduleConfig['settings.']['configPID']) && $this->moduleConfig['settings.']['configPID'] > 0) {
            return (int)$this->moduleConfig['settings.']['configPID'];
        }

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'BackendMissingConfigPID',
                'femanager'
            ),
            'Backend Configuration',
            ContextualFeedbackSeverity::ERROR
        );

        return 0;
    }

    /**
     * @return mixed|void
     * @throws \JsonException
     * @throws SiteNotFoundException
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getFrontendRequestResult(string $status, int $userIdentifier, User $user)
    {
        /** @var SiteFinder $siteFinder */
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        $site = $siteFinder->getSiteByPageId($this->configPID);
        $url = $site->getRouter()->generateUri(
            $this->configPID,
            [
                'tx_femanager_registration' => [
                    'user' => $userIdentifier,
                    'hash' => HashUtility::createHashForUser($user),
                    'status' => $status,
                    'action' => 'confirmCreateRequest',
                    'controller' => 'New',
                ],
            ]
        );
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $response = $requestFactory->request((string)$url, 'GET', ['headers' => ['accept' => 'application/json']]);
        if ($response->getStatusCode() >= 300) {
            $content = $response->getReasonPhrase();
            $GLOBALS['BE_USER']->writelog(4, 0, 1, 0, 'femanager: Frontend request failed.', $content);
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'BackendConfirmationFlashMessageFailed',
                    'femanager'
                ),
                'User Confirmation',
                ContextualFeedbackSeverity::ERROR
            );
        } else {
            $content = $response->getBody()->getContents();
            return json_decode((string)$content, true, 512, JSON_THROW_ON_ERROR);
        }

        return null;
    }

    /**
     * @throws AspectNotFoundException
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function loginAsEnabled(): bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        if ($context->getPropertyFromAspect('backend.user', 'isAdmin') === true) {
            return true;
        }

        $tsConfigEnableLoginAs = (int)($GLOBALS['BE_USER']
            ->getTSConfig()['tx_femanager.']['UserBackend.']['enableLoginAs'] ?? 0);

        return $tsConfigEnableLoginAs === 1;
    }
}
