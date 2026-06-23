<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Service\AutoAdminConfirmationService;
use In2code\Femanager\Domain\Validator\CaptchaValidator;
use In2code\Femanager\Domain\Validator\PasswordValidator;
use In2code\Femanager\Domain\Validator\ServersideValidator;
use In2code\Femanager\Event\BeforeUserConfirmEvent;
use In2code\Femanager\Event\BeforeUserCreateEvent;
use In2code\Femanager\Event\CreateConfirmationRequestEvent;
use In2code\Femanager\Event\UserWasConfirmedByAdminEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class NewController
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewController extends AbstractFrontendController
{

    /**
     * Status values that perform an admin-side state change (enable or delete a pending user) and
     * therefore always require a valid adminHash, independently of the confirmAdminConfirmation setting.
     */
    private const ADMIN_CONFIRMATION_STATUSES = [
        'adminConfirmation',
        'confirmAdmin',
        'adminConfirmationRefused',
        'adminConfirmationRefusedSilent',
        'confirmAdminDeletion',
        'confirmAdminRefused',
        'confirmAdminRefusedSilent',
    ];

    /**
     * Render registration form
     *
     * @throws JsonException
     */
    public function newAction(): ResponseInterface
    {
        $this->view->assignMultiple(
            [
                'allUserGroups' => $this->allUserGroups,
            ]
        );
        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * action create
     *
     * @throws IllegalObjectTypeException
     * @throws InvalidPasswordHashException
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
            throw new PropagateResponseException($this->redirect('createStatus'));
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
            $response = $this->createAllConfirmed($user);
        } else {
            $response = $this->createRequest($user);
        }

        if ($response !== null) {
            return $response;
        }

        return $this->redirect('createStatus');
    }

    /**
     * Dispatcher action for every confirmation request
     *
     * @param int $user User UID (user could be hidden)
     * @param string $hash Given hash
     * @param string $status
     *              "userConfirmation", "userConfirmationRefused", "adminConfirmation",
     *              "adminConfirmationRefused", "adminConfirmationRefusedSilent"
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    /**
     * @return ResponseInterface|void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function confirmCreateRequestAction(int $user, string $hash, string $status = 'adminConfirmation', ?string $adminHash = null)
    {
        $backend = false;
        $user = $this->userRepository->findByUid($user);

        $this->eventDispatcher->dispatch(new BeforeUserConfirmEvent($user, $hash, $status));

        if ($user === null) {
            $this->addFlashMessage(
                LocalizationUtility::translate('missingUserInDatabase'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            throw new PropagateResponseException($this->redirect('new'));
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
            if (str_contains((string)$accept, 'application/json')) {
                $backend = true;
            }
        }

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

        $furtherFunctions = match ($status) {
            'userConfirmation', 'confirmUser' => $this->statusUserConfirmation($user, $hash, $status),
            'userConfirmationRefused', 'confirmDeletion' => $this->statusUserConfirmationRefused($user, $hash),
            'adminConfirmation', 'confirmAdmin' => $this->statusAdminConfirmation($user, $hash, $status, $backend),
            'adminConfirmationRefused', 'adminConfirmationRefusedSilent', 'confirmAdminDeletion', 'confirmAdminRefused', 'confirmAdminDeletionSilent' =>
            $this->statusAdminConfirmationRefused($user, $hash, $status),
            default => false,
        };

        if ($backend) {
            $this->persistenceManager->persistAll();
            $event = new UserWasConfirmedByAdminEvent($request, $user);
            $this->eventDispatcher->dispatch($event);
            /**
             * this request was triggered via Backend Module "Frontend users",
             * so we stop here and provide a feedback to the BE
             */
            echo json_encode(['status' => 'okay']) . PHP_EOL;
            die();
        }

        if ($furtherFunctions) {
            return $this->redirectByAction('new', $status . 'Redirect');
        }

        return $this->redirect('new');
    }

    /**
     * Status action: User confirmation
     *
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function statusUserConfirmation(User $user, string $hash, string $status): bool
    {
        if (HashUtility::validHash($hash, $user)) {
            if ($user->getTxFemanagerConfirmedbyuser()) {
                $this->addFlashMessage(
                    LocalizationUtility::translate('userAlreadyConfirmed'),
                    '',
                    ContextualFeedbackSeverity::ERROR
                );
                throw new PropagateResponseException($this->redirect('new'));
            }

            $user = FrontendUtility::forceValues(
                $user,
                ConfigurationUtility::getValue('new./forceValues./onUserConfirmation.', $this->config)
            );
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
            $this->addFlashMessage(
                LocalizationUtility::translate('createFailedProfile'),
                '',
                ContextualFeedbackSeverity::ERROR
            );

            return false;
        }

        return true;
    }

    /**
     * Status action: User confirmation refused
     *
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     */
    protected function statusUserConfirmationRefused(User $user, string $hash): bool
    {
        if (HashUtility::validHash($hash, $user)) {
            $this->logUtility->log(Log::STATUS_REGISTRATIONREFUSEDUSER, $user);
            $this->addFlashMessage(LocalizationUtility::translate('createProfileDeleted'));
            $this->userRepository->remove($user);
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate('createFailedProfile'),
                '',
                ContextualFeedbackSeverity::ERROR
            );

            return false;
        }

        return true;
    }

    /**
     * Status action: Admin confirmation
     *
     * @param $hash
     * @param $status
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function statusAdminConfirmation(User $user, $hash, $status, bool $backend = false): bool
    {
        if (HashUtility::validHash($hash, $user)) {
            if ($user->getTxFemanagerConfirmedbyadmin()) {
                $this->addFlashMessage(
                    LocalizationUtility::translate('userAlreadyConfirmed'),
                    '',
                    ContextualFeedbackSeverity::ERROR
                );
                throw new PropagateResponseException($this->redirect('new'));
            }

            $user = FrontendUtility::forceValues(
                $user,
                ConfigurationUtility::getValue('new./forceValues./onAdminConfirmation.', $this->config)
            );
            $user->setTxFemanagerConfirmedbyadmin(true);
            $user->setDisable(false);
            $this->userRepository->update($user);
            $this->logUtility->log(Log::STATUS_REGISTRATIONCONFIRMEDADMIN, $user);
            $this->finalCreate($user, 'new', 'createStatus', false, $status, $backend);
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate('createFailedProfile'),
                '',
                ContextualFeedbackSeverity::ERROR
            );

            return false;
        }

        return true;
    }

    /**
     * Status action: Admin refused profile creation (normal or silent)
     *
     * @param $hash
     * @param $status
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     */
    protected function statusAdminConfirmationRefused(User $user, $hash, $status): bool
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
                    ConfigurationUtility::getValue('new./email./createUserNotifyRefused.', $this->config),
                    $this->request
                );
            }
            $this->userRepository->remove($user);
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate('createFailedProfile'),
                '',
                ContextualFeedbackSeverity::ERROR
            );

            return false;
        }

        return true;
    }

    /**
     * Just for showing information after user creation
     */
    public function createStatusAction(): ResponseInterface
    {
        $this->assignForAll();
        return $this->htmlResponse();
    }

    /**
     * Postfix method to createAction(): Create must be confirmed by Admin or User
     *
     * @throws IllegalObjectTypeException
     */
    protected function createRequest(User $user): ResponseInterface|null
    {
        $user->setDisable(true);
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->processUploadedImage($user);
        $this->logUtility->log(Log::STATUS_PROFILECREATIONREQUEST, $user);
        if (!empty($this->settings['new']['confirmByUser'])) {
            $this->createUserConfirmationRequest($user);
            return $this->redirectByAction('new', 'requestRedirect');
        }
        if (!empty($this->settings['new']['confirmByAdmin'])) {
            $this->createAdminConfirmationRequest($user);
            return $this->redirectByAction('new', 'requestRedirect');
        }
        return null;
    }

    /**
     * Send email to user for confirmation
     */
    protected function createUserConfirmationRequest(User $user)
    {
        $this->sendCreateUserConfirmationMail($user);
        $this->addFlashMessage(LocalizationUtility::translate('createRequestWaitingForUserConfirm'));
        return $this->redirectByAction('new', 'requestRedirect');
    }

    /**
     * Send email to admin for confirmation
     */
    protected function createAdminConfirmationRequest(User $user)
    {
        $aacService = GeneralUtility::makeInstance(
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
                ['sender@femanager.org' => 'Sender Name'],
                'New Registration request',
                [
                    'user' => $user,
                    'hash' => HashUtility::createHashForUser($user),
                    'adminHash' => HashUtility::createHashForUser($user, 'admin'),
                ],
                ConfigurationUtility::getValue('new./email./createAdminConfirmation.', $this->config),
                $this->request
            );
            $this->addFlashMessage(LocalizationUtility::translate('createRequestWaitingForAdminConfirm'));
        }
    }

    protected function isAllConfirmed(): bool
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

        if ($user->isDisable() === false || $user->getTxFemanagerConfirmedbyadmin()) {
            return User::CONFIRMATION_REQUIRED_NONE;
        }

        return $user->getTxFemanagerConfirmedbyuser()
            ? User::CONFIRMATION_REQUIRED_ADMIN
            : (User::CONFIRMATION_REQUIRED_USER | User::CONFIRMATION_REQUIRED_ADMIN);
    }

    protected function isAdminConfirmationMissing(User $user): bool
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
     */
    public function resendConfirmationMailAction(): ResponseInterface
    {
        // @todo find a better way to fetch the data
        $result = $this->request->getParsedBody()['tx_femanager_resendconfirmationmail']
            ?? $this->request->getQueryParams()['tx_femanager_resendconfirmationmail']
            ?? $this->request->getParsedBody()['tx_femanager_registration']
            ?? $this->request->getQueryParams()['tx_femanager_registration']
            ?? null;
        $mail = is_array($result) ? ($result['user']['email'] ?? '') : '';

        if ($mail === '' || GeneralUtility::validEmail($mail) === false) {
            $this->addFlashMessage(
                LocalizationUtility::translate('resendConfirmationMailFail'),
                LocalizationUtility::translate('validationError'),
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('resendConfirmationDialogue');
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
            ContextualFeedbackSeverity::INFO
        );
        return $this->redirect('resendConfirmationDialogue');
    }
}
