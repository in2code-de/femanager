<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Event\AdminConfirmationUserEvent;
use In2code\Femanager\Event\RefuseUserEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserBackendController
 */
class UserBackendController extends AbstractController
{
    protected $configPID;

    public function listAction(array $filter = []): ResponseInterface
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $loginAsEnabled = $GLOBALS['BE_USER']->user['admin'] === 1 || (int)$GLOBALS['BE_USER']->getTSConfig(
        )['tx_femanager.']['UserBackend.']['enableLoginAs'] === 1;
        $this->view->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackend($filter),
                'moduleUri' => $uriBuilder->buildUriFromRoute('tce_db'),
                'action' => 'list',
                'loginAsEnabled' => $loginAsEnabled
            ]
        );
        return $this->htmlResponse();
    }

    public function confirmationAction(array $filter = []): ResponseInterface
    {
        $this->configPID = $this->getConfigPID();

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->view->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackendForConfirmation(
                    $filter,
                    ConfigurationUtility::isBackendModuleFilterUserConfirmation()
                ),
                'moduleUri' => $uriBuilder->buildUriFromRoute('tce_db'),
                'action' => 'confirmation'
            ]
        );
        return $this->htmlResponse();
    }

    public function userLogoutAction(User $user): ResponseInterface
    {
        UserUtility::removeFrontendSessionToUser($user);
        $this->addFlashMessage('User successfully logged out');
        $this->redirect('list');
    }

    public function confirmUserAction(int $userIdentifier): ResponseInterface
    {
        $this->configPID = $this->getConfigPID();

        $user = $this->userRepository->findByUid($userIdentifier);
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

        $this->redirect('confirmation');
    }

    public function refuseUserAction(int $userIdentifier)
    {
        $this->configPID = $this->getConfigPID();

        $user = $this->userRepository->findByUid($userIdentifier);
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

        $this->redirect('confirmation');
    }

    public function listOpenUserConfirmationsAction(array $filter = []): ResponseInterface
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->view->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackendForConfirmation(
                    $filter,
                    false
                ),
                'moduleUri' => $uriBuilder->buildUriFromRoute('tce_db'),
                'action' => 'listOpenUserConfirmations'
            ]
        );
        return $this->htmlResponse();
    }

    public function resendUserConfirmationRequestAction(int $userIdentifier)
    {
        $user = $this->userRepository->findByUid($userIdentifier);
        $this->sendCreateUserConfirmationMail($user);
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'BackendConfirmationFlashMessageReSend',
                'femanager',
                [$user->getUsername()]
            ),
            '',
            ContextualFeedbackSeverity::INFO
        );
        $this->redirect('listOpenUserConfirmations');
    }

    /**
     * @return int
     */
    public function getConfigPID()
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
     * @param $status
     * @param $userIdentifier
     * @param $user
     */
    public function getFrontendRequestResult($status, $userIdentifier, $user)
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
                    'controller' => 'New'
                ]
            ]
        );

        $response = GeneralUtility::makeInstance(RequestFactory::class)->request((string)$url, 'GET', ['headers' => ['accept' => 'application/json']]);
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
            return json_decode((string) $content, true, 512, JSON_THROW_ON_ERROR);
        }
    }
}
