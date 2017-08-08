<?php
declare(strict_types=1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InvitationController
 */
class InvitationController extends AbstractController
{

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $this->allowedUserForInvitationNewAndCreate();
        $this->view->assign('allUserGroups', $this->allUserGroups);
        $this->assignForAll();
    }

    /**
     * action create
     *
     * @param User $user
     * @validate $user In2code\Femanager\Domain\Validator\ServersideValidator
     * @validate $user In2code\Femanager\Domain\Validator\PasswordValidator
     * @validate $user In2code\Femanager\Domain\Validator\CaptchaValidator
     * @return void
     */
    public function createAction(User $user)
    {
        $this->allowedUserForInvitationNewAndCreate();
        $user->setDisable(true);
        $user = FrontendUtility::forceValues(
            $user,
            $this->config['invitation.']['forceValues.']['beforeAnyConfirmation.']
        );
        $user = UserUtility::fallbackUsernameAndPassword($user);
        if ($this->settings['invitation']['fillEmailWithUsername'] === '1') {
            $user->setEmail($user->getUsername());
        }
        UserUtility::hashPassword($user, $this->settings['invitation']['misc']['passwordSave']);
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', [$user, $this]);
        $this->createAllConfirmed($user);
    }

    /**
     * Prefix method to createAction()
     *        Create Confirmation from Admin is not necessary
     *
     * @param User $user
     * @return void
     */
    public function createAllConfirmed(User $user)
    {
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->addFlashMessage(LocalizationUtility::translate('createAndInvited'));
        LogUtility::log(Log::STATUS_INVITATIONPROFILECREATED, $user);

        // send confirmation mail to user
        $this->sendMailService->send(
            'invitation',
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            'Profile creation with invitation',
            [
                'user' => $user,
                'settings' => $this->settings,
                'hash' => HashUtility::createHashForUser($user)
            ],
            $this->config['invitation.']['email.']['invitation.']
        );

        // send notify email to admin
        if ($this->settings['invitation']['notifyAdminStep1']) {
            $this->sendMailService->send(
                'invitationNotifyStep1',
                StringUtility::makeEmailArray(
                    $this->settings['invitation']['notifyAdminStep1'],
                    $this->settings['invitation']['email']['invitationAdminNotifyStep1']['receiver']['name']['value']
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile creation with invitation - Step 1',
                [
                    'user' => $user,
                    'settings' => $this->settings
                ],
                $this->config['invitation.']['email.']['invitationAdminNotifyStep1.']
            );
        }
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', [$user, $this]);
        $this->redirectByAction('redirectStep1');
        $this->redirect('new');
    }

    /**
     * action edit
     *
     * @param int $user User UID
     * @param string $hash
     * @return void
     */
    public function editAction($user, $hash = null)
    {
        $user = $this->userRepository->findByUid($user);
        $user->setDisable(false);
        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', [$user, $hash, $this]);
        $this->view->assignMultiple(
            [
                'user' => $user,
                'hash' => $hash
            ]
        );

        if (!HashUtility::validHash($hash, $user)) {
            if ($user !== null) {
                // delete user for security reasons
                $this->userRepository->remove($user);
            }
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', FlashMessage::ERROR);
            $this->forward('status');
        }

        $this->assignForAll();
    }

    /**
     * action update
     *
     * @param \In2code\Femanager\Domain\Model\User $user
     * @validate $user In2code\Femanager\Domain\Validator\ServersideValidator
     * @validate $user In2code\Femanager\Domain\Validator\PasswordValidator
     * @return void
     */
    public function updateAction($user)
    {
        $this->addFlashMessage(LocalizationUtility::translate('createAndInvitedFinished'));
        LogUtility::log(Log::STATUS_INVITATIONPROFILEENABLED, $user);
        if ($this->settings['invitation']['notifyAdmin']) {
            $this->sendMailService->send(
                'invitationNotify',
                StringUtility::makeEmailArray(
                    $this->settings['invitation']['notifyAdmin'],
                    $this->settings['invitation']['email']['invitationAdminNotify']['receiver']['name']['value']
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile creation with invitation - Final',
                [
                    'user' => $user,
                    'settings' => $this->settings
                ],
                $this->config['invitation.']['email.']['invitationAdminNotify.']
            );
        }
        $user = UserUtility::overrideUserGroup($user, $this->settings, 'invitation');
        UserUtility::hashPassword($user, $this->settings['invitation']['misc']['passwordSave']);
        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterPersist', [$user, $this]);
        $this->redirectByAction('invitation', 'redirectPasswordChanged');
        $this->redirect('status');
    }

    /**
     * Init for delete
     *
     * @return void
     */
    protected function initializeDeleteAction()
    {
    }

    /**
     * action delete
     *
     * @param int $user User UID
     * @param string $hash
     * @return void
     */
    public function deleteAction($user, $hash = null)
    {
        $user = $this->userRepository->findByUid($user);

        if (HashUtility::validHash($hash, $user)) {
            LogUtility::log(Log::STATUS_PROFILEDELETE, $user);
            $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_INVITATIONPROFILEDELETEDUSER));

            // send notify email to admin
            if ($this->settings['invitation']['notifyAdminStep1']) {
                $this->sendMailService->send(
                    'invitationRefused',
                    StringUtility::makeEmailArray(
                        $this->settings['invitation']['notifyAdminStep1'],
                        $this->settings['invitation']['email']['invitationRefused']['receiver']['name']['value']
                    ),
                    StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                    'Profile deleted from User after invitation - Step 1',
                    [
                        'user' => $user,
                        'settings' => $this->settings
                    ],
                    $this->config['invitation.']['email.']['invitationRefused.']
                );
            }

            $this->userRepository->remove($user);
            $this->redirectByAction('invitation', 'redirectDelete');
            $this->redirect('status');
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_INVITATIONHASHERROR),
                '',
                FlashMessage::ERROR
            );
            $this->redirect('status');
        }
    }

    /**
     * Restricted Action to show messages
     *
     * @return void
     */
    public function statusAction()
    {
    }

    /**
     * Check if user is allowed to see this action
     *
     * @return bool
     */
    protected function allowedUserForInvitationNewAndCreate()
    {
        if (empty($this->settings['invitation']['allowedUserGroups'])) {
            return true;
        }
        $allowedUsergroupUids = GeneralUtility::trimExplode(
            ',',
            $this->settings['invitation']['allowedUserGroups'],
            true
        );
        $currentUsergroupUids = UserUtility::getCurrentUsergroupUids();

        // compare allowedUsergroups with currentUsergroups
        if (count(array_intersect($allowedUsergroupUids, $currentUsergroupUids))) {
            return true;
        }

        // current user is not allowed
        $this->addFlashMessage(
            LocalizationUtility::translateByState(Log::STATUS_INVITATIONRESTRICTEDPAGE),
            '',
            FlashMessage::ERROR
        );
        $this->forward('status');
        return false;
    }
}
