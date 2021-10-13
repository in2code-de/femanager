<?php

declare(strict_types = 1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Event\AdminConfirmationUserEvent;
use In2code\Femanager\Event\RefuseUserEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserBackendController
 */
class UserBackendController extends AbstractController
{
    protected $configPID;

    /**
     * @param array $filter
     */
    public function listAction(array $filter = [])
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
    }

    /**
     * @param array $filter
     */
    public function confirmationAction(array $filter = [])
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
    }

    /**
     * @param User $user
     */
    public function userLogoutAction(User $user)
    {
        UserUtility::removeFrontendSessionToUser($user);
        $this->addFlashMessage('User successfully logged out');
        $this->redirect('list');
    }

    /**
     * @param int $userIdentifier
     */
    public function confirmUserAction(int $userIdentifier)
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
                AbstractMessage::ERROR
            );
        }

        $this->redirect('confirmation');
    }

    /**
     * @param int $userIdentifier
     */
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
                AbstractMessage::ERROR
            );
        }

        $this->redirect('confirmation');
    }

    /**
     * @param array $filter
     */
    public function listOpenUserConfirmationsAction(array $filter = [])
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
    }

    /**
     * @param int $userIdentifier
     */
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
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
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
            AbstractMessage::ERROR
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
                'tx_femanager_pi1' => [
                    'user' => $userIdentifier,
                    'hash' => HashUtility::createHashForUser($user),
                    'status' => $status,
                    'action' => 'confirmCreateRequest',
                    'controller' => 'New'
                ]
            ]
        );
        $report = [];
        $result = GeneralUtility::getUrl((string)$url, 0, ['accept' => 'application/json'], $report);
        if (!is_string($result)) {
            $GLOBALS['BE_USER']->writelog(4, 0, 1, 0, 'femanager: Frontend request failed.', $report);
            // @todo
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'BackendConfirmationFlashMessageFailed',
                    'femanager'
                ),
                'User Confirmation',
                AbstractMessage::ERROR
            );
        }

        return json_decode($result, true);
    }
}
