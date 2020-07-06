<?php
declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserBackendController
 */
class UserBackendController extends AbstractController
{

    /**
     * @param array $filter
     * @return void
     */
    public function listAction(array $filter = [])
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $loginAsEnabled = $GLOBALS['BE_USER']->user['admin'] === 1 || (int)$GLOBALS['BE_USER']->userTS['tx_femanager.']['UserBackend.']['enableLoginAs'] === 1;

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
     * @return void
     */
    public function confirmationAction(array $filter = [])
    {
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
     * @return void
     */
    public function userLogoutAction(User $user)
    {
        UserUtility::removeFrontendSessionToUser($user);
        $this->addFlashMessage('User successfully logged out');
        $this->redirect('list');
    }

    /**
     * @param int $userIdentifier
     * @return void
     */
    public function confirmUserAction(int $userIdentifier)
    {
        $user = $this->userRepository->findByUid($userIdentifier);
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__, [$user, $this]);
        $user = FrontendUtility::forceValues($user, $this->config['new.']['forceValues.']['onAdminConfirmation.']);
        $user->setTxFemanagerConfirmedbyadmin(true);
        $user->setDisable(false);
        LogUtility::log(Log::STATUS_REGISTRATIONCONFIRMEDADMIN, $user);
        $this->userRepository->update($user);
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'BackendConfirmationFlashMessageConfirmed',
                'femanager',
                [$user->getUsername()]
            )
        );
        $this->finalCreate($user, 'confirmation', 'confirmation', false, '', true);
        $this->redirect('confirmation');
    }

    /**
     * @param int $userIdentifier
     * @return void
     */
    public function refuseUserAction(int $userIdentifier)
    {
        $user = $this->userRepository->findByUid($userIdentifier);
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__, [$user, $this]);
        $this->userRepository->remove($user);
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'BackendConfirmationFlashMessageRefused',
                'femanager',
                [$user->getUsername()]
            )
        );
        $this->redirect('confirmation');
    }

    /**
     * @param array $filter
     * @return void
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
     * @return void
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
            ),'', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
        );
        $this->redirect('listOpenUserConfirmations');
    }

}
