<?php

declare(strict_types=1);

namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Domain\Model\UserGroup;
use In2code\Femanager\Domain\Validator\CaptchaValidator;
use In2code\Femanager\Domain\Validator\PasswordValidator;
use In2code\Femanager\Domain\Validator\ServersideValidator;
use In2code\Femanager\Event\AfterUserUpdateEvent;
use In2code\Femanager\Event\BeforeUpdateUserEvent;
use In2code\Femanager\Event\DeleteUserEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\ObjectUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

/**
 * Class EditController
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditController extends AbstractFrontendController
{
    public function editAction(): ResponseInterface
    {
        $token = '';
        if ($this->user) {
            $token = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Crypto\HashService::class)->hmac((string)$this->user->getUid(), (string)($this->user->getCrdate() ?: new \DateTime('01.01.1970'))->getTimestamp());
        }

        $this->view->assignMultiple([
            'user' => $this->user,
            'allUserGroups' => $this->allUserGroups,
            'token' => $token,
        ]);
        $this->assignForAll();
        return $this->htmlResponse();
    }

    #[Validate(['validator' => ServersideValidator::class, 'param' => 'user'])]
    #[Validate(['validator' => PasswordValidator::class, 'param' => 'user'])]
    #[Validate(['validator' => CaptchaValidator::class, 'param' => 'captcha'])]
    public function updateAction(User $user, string $captcha = null)
    {
        $currentUser = UserUtility::getCurrentUser();
        $userValues = $this->request->getArgument('user') ?? [];
        $token = $this->request->getArgument('token') ?? null;
        $identity = (int)($userValues['__identity'] ?? 0);
        $isSpoof = $this->isSpoof($currentUser, $identity, $token);

        if (!$currentUser instanceof User || $identity === 0 || $token === null || $isSpoof) {
            $logStatus = $isSpoof ? Log::STATUS_PROFILEUPDATEATTEMPTEDSPOOF : Log::STATUS_PROFILEUPDATEREFUSEDSECURITY;
            $logContext = $isSpoof ? $currentUser : $user;
            $this->logUtility->log($logStatus, $logContext);

            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return new ForwardResponse('edit');
        }

        $response = $this->redirectIfNoChangesOnObject($user);
        if ($response instanceof \Psr\Http\Message\ResponseInterface) {
            return $response;
        }

        $user = FrontendUtility::forceValues(
            $user,
            ConfigurationUtility::getValue('edit./forceValues./beforeAnyConfirmation.', $this->config)
        );

        $this->emailForUsername($user);
        UserUtility::convertPassword(
            $user,
            ConfigurationUtility::getValue('edit/misc/passwordSave', $this->settings)
        );

        $this->eventDispatcher->dispatch(new BeforeUpdateUserEvent($user));
        if (!empty($this->settings['edit']['confirmByAdmin'])) {
            $response = $this->updateRequest($user);
        } else {
            $response = $this->updateAllConfirmed($user);
        }

        if ($response !== null) {
            return $response;
        }

        return $this->redirect('edit');
    }

    /**
     * @param User $user User object
     * @param string $hash
     * @param string $status could be "confirm", "refuse", "silentRefuse"
     */
    public function confirmUpdateRequestAction(User $user, $hash, $status = 'confirm'): ResponseInterface
    {
        $this->view->assign('user', $user);
        if (!HashUtility::validHash($hash, $user) || !$user->getTxFemanagerChangerequest()) {
            $this->addFlashMessage(
                LocalizationUtility::translate('updateFailedProfile'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->htmlResponse(null);
        }

        switch ($status) {
            case 'confirm':
                $this->statusConfirm($user);
                break;
            case 'refuse':
                $this->statusRefuse($user);
                break;
            case 'silentRefuse':
                $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user);
                $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDADMIN));
                break;
            default:
        }

        $user->setTxFemanagerChangerequest('');
        $this->userRepository->update($user);

        $this->eventDispatcher->dispatch(new AfterUserUpdateEvent($user, $hash, $status));
        return $this->htmlResponse();
    }

    /**
     * Status update confirmation
     */
    protected function statusConfirm(User $user)
    {
        $values = GeneralUtility::xml2array($user->getTxFemanagerChangerequest());
        foreach ((array)$values as $field => $value) {
            if ($field !== 'usergroup' && method_exists($user, 'set' . ucfirst($field))) {
                $user->{'set' . ucfirst($field)}($value['new']);
            } else {
                $user->removeAllUsergroups();
                $usergroupUids = GeneralUtility::trimExplode(',', $value['new'], true);
                foreach ($usergroupUids as $usergroupUid) {
                    /** @var UserGroup $usergroup */
                    $usergroup = $this->userGroupRepository->findByUid((int)$usergroupUid);
                    $user->addUsergroup($usergroup);
                }
            }
        }

        if (!empty($this->config['edit.']['forceValues.']['onAdminConfirmation.'])) {
            $user = FrontendUtility::forceValues($user, $this->config['edit.']['forceValues.']['onAdminConfirmation.']);
        }

        $this->logUtility->log(Log::STATUS_PROFILEUPDATECONFIRMEDADMIN, $user);
        $this->addFlashMessage(LocalizationUtility::translate('updateProfile'));
    }

    /**
     * Status update refused
     */
    protected function statusRefuse(User $user)
    {
        $this->sendMailService->send(
            'updateRequestRefused',
            StringUtility::makeEmailArray($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()),
            ['sender@femanager.org' => 'Sender Name'],
            'Your change request was refused',
            [
                'user' => $user,
                'settings' => $this->settings,
            ],
            $this->config['edit.']['email.']['updateRequestRefused.'],
            $this->request
        );
        $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDADMIN, $user);
        $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDADMIN));
    }

    /**
     * action delete
     */
    public function deleteAction(User $user): \TYPO3\CMS\Extbase\Http\ForwardResponse|\Psr\Http\Message\ResponseInterface
    {
        $currentUser = UserUtility::getCurrentUser();
        $token = $this->request->hasArgument('token') ? $this->request->getArgument('token') : null;
        $uid = $this->request->hasArgument('user') ? $this->request->getArgument('user') : null;
        if (!$currentUser instanceof \In2code\Femanager\Domain\Model\User ||
            $token === null ||
            $uid === null ||
            $this->isSpoof($currentUser, (int)$uid, $token)
        ) {
            $this->logUtility->log(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY, $user);
            $this->addFlashMessage(
                LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return new ForwardResponse('edit');
        }

        $this->eventDispatcher->dispatch(new DeleteUserEvent($user));
        $this->logUtility->log(Log::STATUS_PROFILEDELETE, $user);
        $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEDELETE));
        $this->userRepository->remove($user);
        return $this->redirectByAction('delete', 'redirect', 'edit');
    }

    /**
     * Check: If there are no changes, simple redirect back
     */
    protected function redirectIfNoChangesOnObject(User $user): ?\Psr\Http\Message\ResponseInterface
    {
        if (!ObjectUtility::isDirtyObject($user, $this->request)) {
            $this->addFlashMessage(LocalizationUtility::translate('noChanges'), '', ContextualFeedbackSeverity::NOTICE);
            return $this->redirect('edit');
        }

        return null;
    }

    protected function emailForUsername(User $user)
    {
        $fillEmailWithUsername = ConfigurationUtility::getValue('edit/fillEmailWithUsername', $this->settings);
        if ($fillEmailWithUsername === '1') {
            $user->setEmail($user->getUsername());
        }
    }
}
