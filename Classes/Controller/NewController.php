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
     * Render registration form
     *
     * @param User|null $user
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

        $user = $this->userRepository->findByUid($user);

        $this->eventDispatcher->dispatch(new BeforeUserConfirmEvent($user, $hash, $status));

        if ($user === null) {
            $this->addFlashMessage(LocalizationUtility::translate('missingUserInDatabase'), '', AbstractMessage::ERROR);
            $this->redirect('new');
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

                    if (!HashUtility::validHash($adminHash, $user, 'admin')) {
                        $this->addFlashMessage(
                            LocalizationUtility::translate('error_not_authorized'),
                            '',
                            ContextualFeedbackSeverity::ERROR
                        );
                        throw new PropagateResponseException($this->redirect('new'), 1743766811);
                    }

                    $this->view->assignMultiple(
                        [
                            'user' => $user,
                            'status' => 'confirmAdmin',
                            'hash' => $hash,
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

                    if (!HashUtility::validHash($adminHash, $user, 'admin')) {
                        $this->addFlashMessage(
                            LocalizationUtility::translate('error_not_authorized'),
                            '',
                            ContextualFeedbackSeverity::ERROR
                        );
                        throw new PropagateResponseException($this->redirect('new'), 1743766811);
                    }

                    $this->view->assignMultiple(
                        [
                            'user' => $user,
                            'status' => 'confirmAdminRefused',
                            'silent' => $status === 'adminConfirmationRefusedSilent',
                            'hash' => $hash,
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
     * @param User $user
     * @return bool
     */
    protected function isAdminConfirmationMissing(User $user)
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
     *
     * @throws UnsupportedRequestTypeException
     * @throws StopActionException
     */
    public function resendConfirmationMailAction()
    {
        // @todo find a better way to fetch the data
        $result = GeneralUtility::_GP('tx_femanager_pi1');
        if (is_array($result)) {
            if (GeneralUtility::validEmail($result['user']['email'])) {
                $user = $this->userRepository->findFirstByEmail($result['user']['email']);
                if (is_a($user, User::class)) {
                    $this->sendCreateUserConfirmationMail($user);
                    $this->addFlashMessage(
                        LocalizationUtility::translate('resendConfirmationMailSend'),
                        '',
                        AbstractMessage::INFO
                    );
                    $this->redirect('resendConfirmationDialogue');
                }
            }
        }
        $this->addFlashMessage(
            LocalizationUtility::translate('resendConfirmationMailFail'),
            LocalizationUtility::translate('validationError'),
            AbstractMessage::ERROR
        );
        $this->redirect('resendConfirmationDialogue');
    }
}
