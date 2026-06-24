<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\AutoAdminConfirmationService;
use In2code\Femanager\Event\BeforeUserConfirmEvent;
use In2code\Femanager\Event\BeforeUserCreateEvent;
use In2code\Femanager\Event\CreateConfirmationRequestEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Event\Mvc\AfterRequestDispatchedEvent;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class NewController
 */
class NewController extends AbstractFrontendController
{
    /**
     * Status values that perform an admin-side state change (enable or delete a pending user) and
     * therefore always require a valid adminHash, independently of the confirmAdminConfirmation setting.
     */
    private const ADMIN_CONFIRMATION_STATUSES = [
        'adminConfirmation',
        'confirmedByAdmin',
        'adminConfirmationRefused',
        'adminConfirmationRefusedSilent',
        'confirmedByAdminRefused',
        'confirmAdminRefusedSilent',
    ];

    /**
     * Render registration form
     *
     */
    public function newAction(): ResponseInterface
    {
        $this->view->assignMultiple(
            [
                'allUserGroups' => $this->allUserGroups
            ]
        );
        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * action create
     *
     * @param User $user
     * @throws InvalidPasswordHashException
     * @throws StopActionException
     * @Validate("In2code\Femanager\Domain\Validator\ServersideValidator", param="user")
     * @Validate("In2code\Femanager\Domain\Validator\PasswordValidator", param="user")
     * @Validate("In2code\Femanager\Domain\Validator\CaptchaValidator", param="user")
     */
    public function createAction(User $user)
    {
        if ($this->ratelimiterService->isLimited()) {
            $this->addFlashMessage(
                LocalizationUtility::translate('ratelimiter_too_many_attempts'),
                '',
                AbstractMessage::ERROR
            );
            $this->redirect('createStatus');
        }
        $user = UserUtility::overrideUserGroup($user, $this->settings);
        $configuration = ConfigurationUtility::getValue('new./forceValues./beforeAnyConfirmation.', $this->config);
        $user = FrontendUtility::forceValues($user, $configuration);
        $user = UserUtility::fallbackUsernameAndPassword($user);
        $user = UserUtility::takeEmailAsUsername($user, $this->settings);

        UserUtility::hashPassword($user, ConfigurationUtility::getValue('new/misc/passwordSave', $this->settings));

        $this->eventDispatcher->dispatch(new BeforeUserCreateEvent($user));
        $this->ratelimiterService->consumeSlot();

        $user->setTxFemanagerConfirmationRequired($this->determineRequiredConfirmation());

        if ($this->isAllConfirmed()) {
            $this->createAllConfirmed($user);
        } else {
            $this->createRequest($user);
        }

        $this->redirect('createStatus');
    }

    /**
     * Dispatcher action for every confirmation request
     *
     * @param int $user User UID (user could be hidden)
     * @param string $hash Given hash
     * @param string $status
     *            "userConfirmation", "userConfirmationRefused", "adminConfirmation",
     *            "adminConfirmationRefused", "adminConfirmationRefusedSilent"
     * @param string $adminHash
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function confirmCreateRequestAction(int $user, string $hash, string $status = 'adminConfirmation', string $adminHash = null)
    {
        $backend = false;
        $furtherFunctions = false;

        $user = $this->userRepository->findByUid($user);

        $this->eventDispatcher->dispatch(new BeforeUserConfirmEvent($user, $hash, $status));

        if ($user === null) {
            $this->addFlashMessage(LocalizationUtility::translate('missingUserInDatabase'), '', AbstractMessage::ERROR);
            $this->redirect('new');
        }

        if (in_array($status, self::ADMIN_CONFIRMATION_STATUSES, true)
            && HashUtility::validHash((string)$adminHash, $user, 'admin') === false
        ) {
            $this->addFlashMessage(
                LocalizationUtility::translate('error_not_authorized'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            throw new PropagateResponseException($this->redirect('new'), 1743766811);
        }

        $request = ServerRequestFactory::fromGlobals();
        // check if the the request was triggered via Backend
        if ($request->hasHeader('Accept')) {
            $accept = $request->getHeader('Accept')[0];
            if (false !== strpos($accept, 'application/json')) {
                $backend = true;
            }
        }

        switch ($status) {
            case 'userConfirmation':
                if ($status === 'userConfirmation' && ConfigurationUtility::getValue(
                        'new./email./createUserConfirmation./confirmUserConfirmation',
                        $this->config
                    ) == '1') {
                    $this->view->assignMultiple(
                        [
                            'user' => $user,
                            'status' => 'confirmUser',
                            'hash' => $hash,
                        ]
                    );
                    $this->assignForAll();
                    return $this->htmlResponse();
                }
                $furtherFunctions = $this->statusUserConfirmation($user, $hash, $status);
                break;

            case 'confirmDeletion':
                $furtherFunctions = $this->statusUserConfirmationRefused($user, $hash);
                break;

            case  'confirmedByUser':
                $furtherFunctions = $this->statusUserConfirmation($user, $hash, $status);
                break;

            case 'userConfirmationRefused':
                if (ConfigurationUtility::getValue('new./email./createUserConfirmation./confirmUserConfirmationRefused', $this->config) == '1') {
                    $this->view->assignMultiple(
                        [
                            'user' => $user,
                            'status' => 'confirmDeletion',
                            'hash' =>$hash
                        ]
                    );
                    $this->assignForAll();
                    return $this->htmlResponse();
                }
                $furtherFunctions = $this->statusUserConfirmationRefused($user, $hash);
                break;

            case 'adminConfirmation':
                if ($status === 'adminConfirmation' && ConfigurationUtility::getValue(
                        'new./email./createUserConfirmation./confirmAdminConfirmation',
                        $this->config
                    ) == '1') {
                    $this->view->assignMultiple(
                        [
                            'user' => $user,
                            'status' => 'confirmAdmin',
                            'hash' => $hash,
                            'adminHash' => $adminHash,
                        ]
                    );
                    $this->assignForAll();
                    return $this->htmlResponse();
                }

                if (($status === 'adminConfirmationRefused' || $status === 'adminConfirmationRefusedSilent') &&
                    ConfigurationUtility::getValue(
                        'new./email./createUserConfirmation./confirmAdminConfirmation',
                        $this->config
                    ) == '1') {
                    $this->view->assignMultiple(
                        [
                            'user' => $user,
                            'status' => 'confirmAdminRefused',
                            'silent' => $status === 'adminConfirmationRefusedSilent',
                            'hash' => $hash,
                            'adminHash' => $adminHash,
                        ]
                    );
                    $this->assignForAll();
                    return $this->htmlResponse();
                }

                $furtherFunctions = $this->statusAdminConfirmation($user, $hash, $status, $backend);
                break;

            case 'confirmedByAdmin':
                $furtherFunctions = $this->statusAdminConfirmation($user, $hash, $status, $backend);
                break;

            case 'confirmedByAdminRefused':
                $furtherFunctions = $this->statusAdminConfirmationRefused($user, $hash, $status, $backend);
                break;

            case 'adminConfirmationRefused':
                // Admin refuses profile
                if ($status === 'userConfirmationRefused' && ConfigurationUtility::getValue(
                        'new./email./createUserConfirmation./confirmUserConfirmationRefused',
                        $this->config
                    ) == '1') {
                    $this->view->assignMultiple(
                        [
                            'user' => $user,
                            'status' => 'confirmDeletion',
                            'hash' => $hash,
                        ]
                    );
                    $this->assignForAll();
                    return $this->htmlResponse();
                }
                break;

            case 'adminConfirmationRefusedSilent':
                $furtherFunctions = $this->statusAdminConfirmationRefused($user, $hash, $status);
                break;

            default:
                $furtherFunctions = false;
        }

        if ($backend) {
            $this->eventDispatcher->dispatch(new AfterRequestDispatchedEvent($this->request, $this->response));
            $this->persistenceManager->persistAll();
            // this request was triggered via Backend Module "Frontend users", so we stop here and provide a feedback to the BE
            echo json_encode(['status' => 'okay']) . PHP_EOL;
            die();
        }

        if ($furtherFunctions) {
            $this->redirectByAction('new', $status . 'Redirect');
        }

        $this->redirect('new');
    }

    /**
     * Status action: User confirmation
     *
     * @param User $user
     * @param string $hash
     * @param string $status
     * @return bool allow further functions
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     */
    protected function statusUserConfirmation(User $user, string $hash, string $status)
    {
        if (HashUtility::validHash($hash, $user)) {
            if ($user->getTxFemanagerConfirmedbyuser()) {
                $this->addFlashMessage(LocalizationUtility::translate('userAlreadyConfirmed'), '', AbstractMessage::ERROR);
                $this->redirect('new');
            }

            $user = FrontendUtility::forceValues($user, ConfigurationUtility::getValue('new./forceValues./onUserConfirmation.', $this->config));
            $user->setTxFemanagerConfirmedbyuser(true);
            $this->userRepository->update($user);
            $this->persistenceManager->persistAll();
            $this->logUtility->log(Log::STATUS_REGISTRATIONCONFIRMEDUSER, $user);

            if ($this->isAdminConfirmationMissing($user)) {
                $this->createAdminConfirmationRequest($user);
            } else {
                $user->setDisable(false);
                $this->logUtility->log(Log::STATUS_NEWREGISTRATION, $user);
                $this->finalCreate($user, 'new', 'createStatus', true, $status);
            }
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', AbstractMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * Status action: User confirmation refused
     *
     * @param User $user
     * @param string $hash
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     */
    protected function statusUserConfirmationRefused(User $user, $hash)
    {
        if (HashUtility::validHash($hash, $user)) {
            $this->logUtility->log(Log::STATUS_REGISTRATIONREFUSEDUSER, $user);
            $this->addFlashMessage(LocalizationUtility::translate('createProfileDeleted'));
            $this->userRepository->remove($user);
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', AbstractMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * Status action: Admin confirmation
     *
     * @param User $user
     * @param string $hash
     * @param string $status
     * @return bool allow further functions
     */
    protected function statusAdminConfirmation(User $user, $hash, $status, $backend = false)
    {
        if (HashUtility::validHash($hash, $user)) {
            if ($user->getTxFemanagerConfirmedbyadmin()) {
                $this->addFlashMessage(LocalizationUtility::translate('userAlreadyConfirmed'), '', AbstractMessage::ERROR);
                $this->redirect('new');
            }

            $user = FrontendUtility::forceValues($user, ConfigurationUtility::getValue('new./forceValues./onAdminConfirmation.', $this->config));
            $user->setTxFemanagerConfirmedbyadmin(true);
            $user->setDisable(false);
            $this->userRepository->update($user);
            $this->logUtility->log(Log::STATUS_REGISTRATIONCONFIRMEDADMIN, $user);
            $this->finalCreate($user, 'new', 'createStatus', false, $status, $backend);
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', AbstractMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * Status action: Admin refused profile creation (normal or silent)
     *
     * @param User $user
     * @param $hash
     * @param $status
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     */
    protected function statusAdminConfirmationRefused(User $user, $hash, $status)
    {
        if (HashUtility::validHash($hash, $user)) {
            $this->logUtility->log(Log::STATUS_REGISTRATIONREFUSEDADMIN, $user);
            $this->addFlashMessage(LocalizationUtility::translate('createProfileDeleted'));
            if ($status !== 'adminConfirmationRefusedSilent') {
                $this->sendMailService->send(
                    'CreateUserNotifyRefused',
                    StringUtility::makeEmailArray(
                        $user->getEmail(),
                        $user->getFirstName() . ' ' . $user->getLastName()
                    ),
                    ['sender@femanager.org' => 'Sender Name'],
                    'Your profile was refused',
                    ['user' => $user],
                    ConfigurationUtility::getValue('new./email./createUserNotifyRefused.', $this->config)
                );
            }
            $this->userRepository->remove($user);
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', AbstractMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * Just for showing informations after user creation
     */
    public function createStatusAction(): ResponseInterface
    {
        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * Postfix method to createAction(): Create must be confirmed by Admin or User
     *
     * @param User $user
     */
    protected function createRequest(User $user)
    {
        $user->setDisable(true);
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->logUtility->log(Log::STATUS_PROFILECREATIONREQUEST, $user);
        if (!empty($this->settings['new']['confirmByUser'])) {
            $this->createUserConfirmationRequest($user);
            $this->redirectByAction('new', 'requestRedirect');
        } elseif (!empty($this->settings['new']['confirmByAdmin'])) {
            $this->createAdminConfirmationRequest($user);
            $this->redirectByAction('new', 'requestRedirect');
        }
    }

    /**
     * Send email to user for confirmation
     *
     * @param User $user
     * @throws UnsupportedRequestTypeException
     */
    protected function createUserConfirmationRequest(User $user)
    {
        $this->sendCreateUserConfirmationMail($user);
        $this->addFlashMessage(LocalizationUtility::translate('createRequestWaitingForUserConfirm'));
        $this->redirectByAction('new', 'requestRedirect');
    }

    /**
     * Send email to admin for confirmation
     *
     * @param User $user
     * @throws UnsupportedRequestTypeException
     */
    protected function createAdminConfirmationRequest(User $user)
    {
        $aacService = $this->objectManager->get(
            AutoAdminConfirmationService::class,
            $user,
            $this->settings,
            $this->contentObject
        );
        if ($aacService->isAutoAdminConfirmationFullfilled()) {
            $user->setDisable(false);
            $this->eventDispatcher->dispatch(
                new CreateConfirmationRequestEvent($user, CreateConfirmationRequestEvent::MODE_AUTOMATIC)
            );
            $this->createAllConfirmed($user);
        } else {
            $this->eventDispatcher->dispatch(
                new CreateConfirmationRequestEvent($user, CreateConfirmationRequestEvent::MODE_MANUAL)
            );
            $this->sendMailService->send(
                'createAdminConfirmation',
                StringUtility::makeEmailArray(
                    $this->settings['new']['confirmByAdmin'] ?? '',
                    $this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value'] ?? ''
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'New Registration request',
                [
                    'user' => $user,
                    'hash' => HashUtility::createHashForUser($user),
                    'adminHash' => HashUtility::createHashForUser($user, 'admin'),
                ],
                ConfigurationUtility::getValue('new./email./createAdminConfirmation.', $this->config)
            );
            $this->addFlashMessage(LocalizationUtility::translate('createRequestWaitingForAdminConfirm'));
        }
    }

    /**
     * @return bool
     */
    protected function isAllConfirmed()
    {
        return empty($this->settings['new']['confirmByUser']) && empty($this->settings['new']['confirmByAdmin']);
    }

    /**
     * Build the confirmation bitmask from the registration settings. This is evaluated once during
     * registration (where the settings reliably belong to the registration plugin) and persisted on
     * the user, so later steps no longer depend on the ambient plugin settings.
     */
    protected function determineRequiredConfirmation(): int
    {
        $confirmationRequired = User::CONFIRMATION_REQUIRED_NONE;
        if (!empty($this->settings['new']['confirmByUser'])) {
            $confirmationRequired |= User::CONFIRMATION_REQUIRED_USER;
        }
        if (!empty($this->settings['new']['confirmByAdmin'])) {
            $confirmationRequired |= User::CONFIRMATION_REQUIRED_ADMIN;
        }

        return $confirmationRequired;
    }

    /**
     * Effective confirmation bitmask for a user. Accounts created before the
     * tx_femanager_confirmation_required field existed carry the stored value NONE; for those the
     * requirement is inferred from the confirmation state - mirroring ConfirmationRequiredUpdater - so
     * the workflow stays correct even when the upgrade wizard has not been executed yet.
     */
    protected function getEffectiveConfirmationRequired(User $user): int
    {
        $stored = $user->getTxFemanagerConfirmationRequired();
        if ($stored !== User::CONFIRMATION_REQUIRED_NONE) {
            return $stored;
        }

        if ($user->getDisable() === false || $user->getTxFemanagerConfirmedbyadmin()) {
            return User::CONFIRMATION_REQUIRED_NONE;
        }

        return $user->getTxFemanagerConfirmedbyuser()
            ? User::CONFIRMATION_REQUIRED_ADMIN
            : (User::CONFIRMATION_REQUIRED_USER | User::CONFIRMATION_REQUIRED_ADMIN);
    }

    /**
     * @param User $user
     * @return bool
     */
    protected function isAdminConfirmationMissing(User $user)
    {
        return $user->getTxFemanagerConfirmedbyadmin() === false
            && ($this->getEffectiveConfirmationRequired($user) & User::CONFIRMATION_REQUIRED_ADMIN) !== 0;
    }

    /**
     * The resend action (re)sends the *user* confirmation link. It must only do so for accounts that
     * actually still await a user confirmation. The resend plugin has no knowledge of the registration
     * settings, so the decision is taken from the user record - otherwise a valid confirmation link
     * could be handed out for admin-only or already confirmed accounts.
     */
    protected function isUserConfirmationResendable(User $user): bool
    {
        return $user->getTxFemanagerConfirmedbyuser() === false
            && ($this->getEffectiveConfirmationRequired($user) & User::CONFIRMATION_REQUIRED_USER) !== 0;
    }

    /**
     * Just for showing empty dialogue to resend confirmation mail
     */
    public function resendConfirmationDialogueAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * re-sends a confirmation email if given mail is valid
     *
     * @throws UnsupportedRequestTypeException
     * @throws StopActionException
     */
    public function resendConfirmationMailAction()
    {
        // @todo find a better way to fetch the data
        $result = GeneralUtility::_GP('tx_femanager_pi1');
        $mail = is_array($result) ? ($result['user']['email'] ?? '') : '';

        if ($mail === '' || GeneralUtility::validEmail($mail) === false) {
            $this->addFlashMessage(
                LocalizationUtility::translate('resendConfirmationMailFail'),
                LocalizationUtility::translate('validationError'),
                AbstractMessage::ERROR
            );
            $this->redirect('resendConfirmationDialogue');
        }

        // A confirmation mail is only sent when the account actually has a pending user confirmation.
        // The response is identical for every valid address (sent, nothing to send, or no such
        // account), so it cannot be used to find out whether an account exists for a given email.
        $user = $this->userRepository->findFirstByEmail($mail);
        if ($user instanceof User && $this->isUserConfirmationResendable($user)) {
            $this->sendCreateUserConfirmationMail($user);
        }

        $this->addFlashMessage(
            LocalizationUtility::translate('resendConfirmationMailSend'),
            '',
            AbstractMessage::INFO
        );
        $this->redirect('resendConfirmationDialogue');
    }
}
