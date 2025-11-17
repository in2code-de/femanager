<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Validator\CaptchaValidator;
use In2code\Femanager\Domain\Validator\PasswordValidator;
use In2code\Femanager\Domain\Validator\ServersideValidator;
use In2code\Femanager\Event\InviteUserConfirmedEvent;
use In2code\Femanager\Event\InviteUserCreateEvent;
use In2code\Femanager\Event\InviteUserEditEvent;
use In2code\Femanager\Event\InviteUserUpdateEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;

/**
 * Class InvitationController
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvitationController extends AbstractFrontendController
{

    /**
     * action new
     */
    public function newAction(): ResponseInterface
    {
        if ($this->allowedUserForInvitationNewAndCreate() !== true) {
            // current user is not allowed
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_INVITATIONRESTRICTEDPAGE),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('status');
        }
        $this->view->assign('allUserGroups', $this->allUserGroups);
        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * action create
     */
    #[Validate(['validator' => ServersideValidator::class, 'param' => 'user'])]
    #[Validate(['validator' => PasswordValidator::class, 'param' => 'user'])]
    #[Validate(['validator' => CaptchaValidator::class, 'param' => 'captcha'])]
    public function createAction(User $user, string $captcha = null): ResponseInterface
    {
        if ($this->ratelimiterService->isLimited()) {
            $this->addFlashMessage(
                LocalizationUtility::translate('ratelimiter_too_many_attempts'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            $this->redirect('status');
        }

        if ($this->allowedUserForInvitationNewAndCreate() !== true) {
            // current user is not allowed
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_INVITATIONRESTRICTEDPAGE),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('status');
        }
        $user->setDisable(true);
        $user = FrontendUtility::forceValues(
            $user,
            ConfigurationUtility::getValue('invitation./forceValues./beforeAnyConfirmation.', $this->config)
        );
        $user = UserUtility::fallbackUsernameAndPassword($user);
        if (ConfigurationUtility::getValue('invitation/fillEmailWithUsername', $this->settings) === '1') {
            $user->setEmail($user->getUsername());
        }
        UserUtility::hashPassword(
            $user,
            ConfigurationUtility::getValue('invitation/misc/passwordSave', $this->settings)
        );
        $this->eventDispatcher->dispatch(new InviteUserCreateEvent($user));
        $this->ratelimiterService->consumeSlot();
        return $this->createAllConfirmed($user);
    }

    /**
     * Prefix method to createAction()
     *        Create Confirmation from Admin is not necessary
     */
    public function createAllConfirmed(User $user): ResponseInterface
    {
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->processUploadedImage($user);
        $this->addFlashMessage(LocalizationUtility::translate('createAndInvited'));
        $this->logUtility->log(Log::STATUS_INVITATIONPROFILECREATED, $user);

        // send confirmation mail to user
        $this->sendMailService->send(
            'invitation',
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
            'Profile creation with invitation',
            [
                'user' => $user,
                'settings' => $this->settings,
                'hash' => HashUtility::createHashForUser($user),
            ],
            $this->config['invitation.']['email.']['invitation.'],
            $this->request
        );

        // send notify email to admin
        $notifyAdminStep1 = ConfigurationUtility::getValue('invitation/notifyAdminStep1', $this->settings);
        if ($notifyAdminStep1) {
            $this->sendMailService->send(
                'invitationNotifyStep1',
                StringUtility::makeEmailArray(
                    $notifyAdminStep1,
                    ConfigurationUtility::getValue(
                        'invitation/email/invitationAdminNotifyStep1/receiver/name/value',
                        $this->settings
                    )
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile creation with invitation - Step 1',
                [
                    'user' => $user,
                    'settings' => $this->settings,
                ],
                ConfigurationUtility::getValue('invitation./email./invitationAdminNotifyStep1.', $this->config),
                $this->request
            );
        }

        $this->eventDispatcher->dispatch(new InviteUserConfirmedEvent($user));

        return $this->redirectByAction('invitation', 'redirectStep1');
    }

    /**
     * action edit
     */
    public function editAction(int $user, string $hash = ''): ResponseInterface
    {
        $user = $this->userRepository->findByUid($user);

        // User must exist and hash must be valid
        if ($user === null || !HashUtility::validHash($hash, $user)) {
            $this->addFlashMessage(
                LocalizationUtility::translate('createFailedProfile'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            $this->redirect('status');
        }

        // User must not be deleted (deleted = 0) and not be activated (disable = 1)
        if ($user->isDisable() == 0) {
            $this->addFlashMessage(
                LocalizationUtility::translate('userAlreadyConfirmed'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            $this->redirect('status');
        }

        $user->setDisable(false);
        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(new InviteUserEditEvent($user, $hash));

        $this->view->assignMultiple(
            [
                'user' => $user,
                'hash' => $hash,
            ]
        );

        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * action update
     */
    #[Validate(['validator' => ServersideValidator::class, 'param' => 'user'])]
    #[Validate(['validator' => PasswordValidator::class, 'param' => 'user'])]
    public function updateAction(User $user, string $hash = ''): ResponseInterface
    {
        if (!HashUtility::validHash($hash, $user)) {
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            $this->redirect('status');
        }
        $this->addFlashMessage(LocalizationUtility::translate('createAndInvitedFinished'));
        $this->logUtility->log(Log::STATUS_INVITATIONPROFILEENABLED, $user);
        $notifyAdmin = ConfigurationUtility::getValue('invitation/notifyAdmin', $this->settings);
        if ($notifyAdmin) {
            $this->sendMailService->send(
                'invitationNotify',
                StringUtility::makeEmailArray(
                    $notifyAdmin,
                    ConfigurationUtility::getValue(
                        'invitation/email/invitationAdminNotify/receiver/name/value',
                        $this->settings
                    )
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile creation with invitation - Final',
                [
                    'user' => $user,
                    'settings' => $this->settings,
                ],
                ConfigurationUtility::getValue('invitation./email./invitationAdminNotify.', $this->config),
                $this->request
            );
        }
        $user = UserUtility::overrideUserGroup($user, $this->settings, 'invitation');
        UserUtility::hashPassword(
            $user,
            ConfigurationUtility::getValue('invitation/misc/passwordSave', $this->settings)
        );
        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();
        $this->eventDispatcher->dispatch(new InviteUserUpdateEvent($user));
        return $this->redirectByAction('invitation', 'redirectPasswordChanged', 'status');
    }

    /**
     * action delete
     */
    public function deleteAction(int $user, string $hash = ''): ResponseInterface
    {
        $user = $this->userRepository->findByUid($user);

        if($this->addVariablesForActionConfirmation(true, $user, 'invitationConfirmationRefused')) {
            return $this->htmlResponse();
        };

        if ($user !== null && HashUtility::validHash($hash, $user)) {
            $this->logUtility->log(Log::STATUS_PROFILEDELETE, $user);
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_INVITATIONPROFILEDELETEDUSER)
            );

            // send notify email to admin
            $notifyAdmin = ConfigurationUtility::getValue('invitation/notifyAdmin', $this->settings);
            if ($notifyAdmin) {
                $this->sendMailService->send(
                    'invitationRefused',
                    StringUtility::makeEmailArray(
                        $notifyAdmin,
                        ConfigurationUtility::getValue(
                            'invitation/email/invitationRefused/receiver/name/value',
                            $this->settings
                        )
                    ),
                    StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                    'Profile deleted from User after invitation - Step 1',
                    [
                        'user' => $user,
                        'settings' => $this->settings,
                    ],
                    $this->config['invitation.']['email.']['invitationRefused.'],
                    $this->request
                );
            }

            $this->userRepository->remove($user);
            return $this->redirectByAction('invitation', 'redirectDelete', 'status');
        }
        $this->addFlashMessage(
            LocalizationUtility::translateByState(Log::STATUS_INVITATIONHASHERROR),
            '',
            ContextualFeedbackSeverity::ERROR
        );
        return $this->redirect('status');
    }

    /**
     * Restricted Action to show messages
     */
    public function statusAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * Check if user is allowed to see this action
     */
    protected function allowedUserForInvitationNewAndCreate(): bool
    {
        if (empty($this->settings['invitation']['allowedUserGroups'] ?? [])) {
            return true;
        }
        $allowedUsergroupUids = GeneralUtility::trimExplode(
            ',',
            $this->settings['invitation']['allowedUserGroups'] ?? [],
            true
        );
        $currentUsergroupUids = UserUtility::getCurrentUsergroupUids();

        // compare allowedUsergroups with currentUsergroups
        if (count(array_intersect($allowedUsergroupUids, $currentUsergroupUids))) {
            return true;
        }
        return false;
    }
}
