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
use TYPO3\CMS\Core\Http\JsonResponse;
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
     * Render registration form
     *
     * @param User|null $user
     * @throws JsonException
     */
    public function newAction(User $user = null): ResponseInterface
    {
        $this->view->assignMultiple(
            [
                'user' => $user,
                'allUserGroups' => $this->allUserGroups
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
    #[Validate(['validator' => CaptchaValidator::class, 'param' => 'user'])]
    public function createAction(User $user): ResponseInterface
    {

        if ($this->ratelimiterService->isLimited()) {
            $this->addFlashMessage(
                LocalizationUtility::translate('ratelimiter_too_many_attempts'),
                '',
                ContextualFeedbackSeverity::ERROR
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
    public function confirmCreateRequestAction(int $user, string $hash, string $status = 'adminConfirmation')
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
            $this->redirect('new');
        }

        $request = ServerRequestFactory::fromGlobals();
        // check if the request was triggered via Backend
        if ($request->hasHeader('Accept')) {
            $accept = $request->getHeader('Accept')[0];
            if (str_contains((string) $accept, 'application/json')) {
                $backend = true;
            }
        }

        $furtherFunctions = match ($status) {
            'userConfirmation' => $this->statusUserConfirmation($user, $hash, $status),
            'userConfirmationRefused' => $this->statusUserConfirmationRefused($user, $hash),
            'adminConfirmation' => $this->statusAdminConfirmation($user, $hash, $status, $backend),
            'adminConfirmationRefused', 'adminConfirmationRefusedSilent' =>
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
                $this->redirect('new');
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
                $this->redirect('new');
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
        return $this->htmlResponse();
    }

    /**
     * Postfix method to createAction(): Create must be confirmed by Admin or User
     *
     * @return void
     * @throws IllegalObjectTypeException
     */
    protected function createRequest(User $user): ResponseInterface|null
    {
        $user->setDisable(true);
        $this->userRepository->add($user);
        $this->persistenceManager->persistAll();
        $this->logUtility->log(Log::STATUS_PROFILECREATIONREQUEST, $user);
        if (!empty($this->settings['new']['confirmByUser'])) {
            $this->createUserConfirmationRequest($user);
            return $this->redirectByAction('new', 'requestRedirect');
        } elseif (!empty($this->settings['new']['confirmByAdmin'])) {
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
     *
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
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'New Registration request',
                [
                    'user' => $user,
                    'hash' => HashUtility::createHashForUser($user)
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

    protected function isAdminConfirmationMissing(User $user): bool
    {
        return !empty($this->settings['new']['confirmByAdmin']) && !$user->getTxFemanagerConfirmedbyadmin();
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
    public function resendConfirmationMailAction()
    {
        // @todo find a better way to fetch the data
        $result = GeneralUtility::_GP('tx_femanager_registration');
        if (is_array($result)) {
            $mail = $result['user']['email'] ?? '';
            if ($mail && GeneralUtility::validEmail($mail)) {
                $user = $this->userRepository->findFirstByEmail($mail);
                if (is_a($user, User::class)) {
                    $this->sendCreateUserConfirmationMail($user);
                    $this->addFlashMessage(
                        LocalizationUtility::translate('resendConfirmationMailSend'),
                        '',
                        ContextualFeedbackSeverity::INFO
                    );
                    $this->redirect('resendConfirmationDialogue');
                }
            }
        }
        $this->addFlashMessage(
            LocalizationUtility::translate('resendConfirmationMailFail'),
            LocalizationUtility::translate('validationError'),
            ContextualFeedbackSeverity::ERROR
        );
        $this->redirect('resendConfirmationDialogue');
    }
}
